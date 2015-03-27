<?php
require 'Predis/Autoloader.php';
Predis\Autoloader::register();

class MusicRxAPI{
	function student_20000_recommendation($s_id){
		$date = new DateTime();
		$t1 = $date->format('U = Y-m-d H:i:s') . "\n";
		$history=DB::sql("SELECT track,suitable FROM student_tracks where student_id='$s_id'");
		//'track' is 'id' in songs DB
		$history_id = fopen("/home/jiakun/exercise/search/hist/".$s_id.".his", "w") or die("Unable to open file!");
		if(count($history) > 0){
			$num_history = count($history);
			for($i = 0; $i < $num_history; $i++){
				fwrite($history_id, $history[$i]['track']." ".$history[$i]['suitable']."\r\n");
			}
			
		}
		else{
			fwrite($history_id, "first random\r\n");
		}
		fclose($history_id);
		$date = new DateTime();
		$t2 = $date->format('U = Y-m-d H:i:s') . "\n";
		
		//
		// R recommendation programming
		//
		$algorithm = "bandit_cf_xingzhe";
		$R_PATH = "/home/jiakun/MusicRx/xingzhe/Bayes/R";
        $cmd = sprintf("Rscript %s/%s.R  %s", $R_PATH, $algorithm, $s_id);
        error_log($cmd);        
		$cmd_result = shell_exec($cmd);
		$cmd_result = substr($cmd_result, 0, -1);	//delete redundant ' ' at the end of str
		$date = new DateTime();
		$t3 = $date->format('U = Y-m-d H:i:s') . "\n";

		$song_id = explode("\t",$cmd_result);
		$song_id=MusicRxAPI::join_conn($song_id);
		//var_dump(result.$song_id);
		
		//? maybe use other id-connection
		$result = DB::sql("SELECT * FROM songs WHERE id IN $song_id");
		//var_dump($result);
		$date = new DateTime();
		$t4 = $date->format('U = Y-m-d H:i:s') . "\n";
		MusicRxAPI::save_runtime($t1,$t2,$t3,$t4,$s_id);
		return $result;
		
	}
	function student_old_recommendation($s_id){
	// use old system to recommend songs to user
	// record the recommendation duration

		$date = new DateTime();
		$t1 = $date->format('U = Y-m-d H:i:s') . "\n";
		$history=DB::sql("SELECT track,suitable FROM student_tracks where student_id='$s_id'");
		//var_dump($history);
		$history_id = fopen("/home/jiakun/exercise/search/hist/".$s_id.".his", "w") or die("Unable to open file!");
		// Create history file
		// use track_7digital_id id-connection
		$echonest_id_array = array();
		foreach($history as $id){
			array_push($echonest_id_array, $id['track']);
		}
		$echonest_id_conn=MusicRxAPI::join_conn($echonest_id_array);
		$track_7digital_id=DB::sql("SELECT track_7digital_id FROM previews WHERE echonest_id IN $echonest_id_conn");
		$num_history = count($track_7digital_id);
		$track_7digital_id_conn=MusicRxAPI::join_conn($track_7digital_id);
		$song_id = DB::sql("SELECT id FROM songs WHERE 7digitalID IN $track_7digital_id_conn");
		//var_dump(song_id.$song_id);
		for($i = 0; $i < $num_history; $i++){
			fwrite($history_id, $song_id[$i]['id']." ".$history[$i]['suitable']."\r\n");
		}
		fclose($history_id);
		//var_dump('finish history');
		$date = new DateTime();
		$t2 = $date->format('U = Y-m-d H:i:s') . "\n";

		$result = array();
		$final_result = array();
		//
		// R recommendation programming
		//
		//$algorithm = "bandit_cf_gibbs";
		$algorithm = "bandit_cf_xingzhe";
		$R_PATH = "/home/jiakun/MusicRx/xingzhe/Bayes/R";
        $cmd = sprintf("Rscript %s/%s.R  %s", $R_PATH, $algorithm, $s_id);
        error_log($cmd);        
		$cmd_result = shell_exec($cmd);
		$cmd_result = substr($cmd_result, 0, -1);	//delete redundant ' ' at the end of str
		
		//var_dump('finish recom');
		$date = new DateTime();
		$t3 = $date->format('U = Y-m-d H:i:s') . "\n";
		//var_dump($t3);
		$song_id = explode("\t",$cmd_result);
		$song_id=MusicRxAPI::join_conn($song_id);
		//var_dump(result.$song_id);
		$final_result = array();
		
		//? maybe use other id-connection
		$result_7digitalIDs = DB::sql("SELECT 7digitalID FROM songs WHERE id IN $song_id");
		var_dump($result_7digitalIDs);
		foreach($result_7digitalIDs as $result_7digitalID){
			$t_7digitalID = $result_7digitalID['7digitalID'];
			var_dump($t_7digitalID);
			$result = DB::sql("SELECT song_id FROM previews WHERE track_7digital_id = $t_7digitalID");
			$song_id=$result[0]['song_id'];
			array_push($final_result,$song_id);
		}	
		
		$date = new DateTime();
		$t4 = $date->format('U = Y-m-d H:i:s') . "\n";
		//var_dump($t4);
		//var_dump(count($pre_result));
		MusicRxAPI::save_runtime($t1,$t2,$t3,$t4,$s_id);
		var_dump($final_result);
		return $final_result;
	}
	
	function student_filter($s_id){
		$date = new DateTime();
		$tstart = $date->format('U = Y-m-d H:i:s') . "\n";
		
		$track_title=array();
		$track_other=array();
		// read the assessment from database
		//var_dump($s_id);
		$student=DB::sql("select * from students where id='$s_id'");
		//var_dump($student[0]['artist']);
		$lang_tag_id=array();
		
		$artist=$student[0]['artist']; //artist, song, composer
		if($artist==""){
			$artist_id=array();
			$song_id1=array();
		}
		else{
			$artist=MusicRxAPI::split_refine($artist,",");
			$artist_conn=MusicRxAPI::join_conn($artist);
			$artist_id=DB::sql("SELECT id FROM artists WHERE lower(name) IN $artist_conn");
			//var_dump(intval($artist_id[0]["id"]));
			// find the corresponding id for the song
			$song_title=$artist;
			//var_dump($song_title);
			$song_conn=MusicRxAPI::join_conn($song_title);
			//var_dump($song_conn);
			$song_id1=DB::sql("SELECT song_id FROM previews WHERE lower(track_name) IN $song_conn");
		}
		//var_dump($song_id1);
	
		// find the corresponding id for the tags
		
		$genre=$student[0]['genre']; //artist, song, composer
		$genre_tag=array();
		if($genre==""){
			$genre_id=array();
			$song_id2=array();
		}
		else{
			$genres=MusicRxAPI::split_refine($genre,",");
			foreach($genres as $single_genre){
				if(strpos(strtolower($single_genre),"classic") !== false){
					array_push($genre_tag,"classic");
				}
				if(strpos(strtolower($single_genre),"pop") !== false){
					array_push($genre_tag,"pop");
				}
			}
		}
		//var_dump($genre_tag);
		// find the corresponding id for the genre_tag
		if(count($genre_tag)==0){
			$genre_tag_id=array();
		}
		else{
			$genre_conn=MusicRxAPI::connection($genre_tag);
			$genre_tag_id=DB::sql("SELECT id FROM tags WHERE tag regexp '$genre_conn' and number>500 order by number DESC"); //sort by number
		}
	//$duaration="";
		//var_dump($genre_tag_id[0]["id"]);

		//$tempo=$patient[0]['tempo_range'];
		//$tempo=MusicRxAPI::split_refine($tempo,"-");
		//$min_tempo=intval($tempo[0]);
		//$max_tempo=intval($tempo[1]);
		//if($min_tempo!=0){
		//	$rate_from=$min_tempo;
		//}
		//if($max_tempo!=0){
		//	$rate_to=$max_tempo;
		//}
	//var_dump($rate_from);
	//var_dump($rate_to);

		
	//title
		$song_id2=array();

		$song_id=array();
		$song_id=array_merge($song_id1,$song_id2);
	//var_dump($song_id);

		$stable_duration_from=60; $stable_duration_to=2400; 
		$stable_percent_from=80; $stable_percent_to=100; 
		$run_percent_from=66.77; $run_percent_to=100; 
		$rate_from=50;$rate_to=250;
		$meter_from=3; $meter_to=4; 
		$max_tempo_drift_from=0; $max_tempo_drift_to=5; 
		$max_percent_deviation_from=0.01; $max_percent_deviation_to=5; 
		$max_successive_change_from=0; $max_successive_change_to=5;
		$mismatch_from=-24.63; $mismatch_to=55.28;
		$loudness_from=-100; $loudness_to=100; 
		$danceability_from=0; $danceability_to=1; 
		$year_from=1800; $year_to=2013;
		$key="stable_duration";

		$tempo_level=(int)$student[0]["speed"];
		if($tempo_level == 1){
			//$rate_from = 50;$rate_to = 140;
			$rate_from = 50;$rate_to = 100;
		}
		if($tempo_level == 2){
			//$rate_from = 80;$rate_to = 160;
			$rate_from = 75;$rate_to = 125;
		}
		if($tempo_level == 3){
			$rate_from = 100;$rate_to = 150;
		}
		if($tempo_level == 4){
			$rate_from = 125;$rate_to = 175;
		}
		if($tempo_level == 5){
			$rate_from = 150;$rate_to = 200;
		}
		$loudness_level=(int)$student[0]["loudness"];
		//if($loudness_level == 1){
		//$loudness_from = -55;$loudness_to = -35;
		//}
		//if($loudness_level == 2){
		//	$loudness_from = -45;$loudness_to = -25;
		//}
		//if($loudness_level == 3){
		//	$loudness_from = -35;$loudness_to = -15;
		//}
		//if($loudness_level == 4){
		//	$loudness_from = -25;$loudness_to = -5;
		//}
		//if($loudness_level == 5){
		//	$loudness_from = -15;$loudness_to = 5;
		//}
		

		//var_dump('tempo from'.$rate_from.'to'.$rate_to);
		//('loudnessfrom'.$loudness_from.'to'.$loudness_to);
		//var_dump($artist);
	// Query
		$query1 = sprintf("0 %f %f %f %f %f %f %f %f %f %f %f %f %f %f %f %f %f %f %f %f %f %f
		%d %d 
		%d %d %d %d %d %d %d %d %d %d 
		%d %d %d %d %d %d %d %d %d %d",
		$stable_duration_from, $stable_duration_to, 
		$stable_percent_from, $stable_percent_to, 
		$run_percent_from, $run_percent_to, 
		$rate_from, $rate_to, 
		$meter_from, $meter_to, 
		$max_tempo_drift_from, $max_tempo_drift_to, 
		$max_percent_deviation_from, $max_percent_deviation_to, 
		$max_successive_change_from, $max_successive_change_to,
		$mismatch_from, $mismatch_to,
		$loudness_from, $loudness_to, 
		$danceability_from, $danceability_to, 
		$year_from, $year_to,

		intval($artist_id[0]["id"]), intval($artist_id[1]["id"]), intval($artist_id[2]["id"]),
		intval($artist_id[3]["id"]), intval($artist_id[4]["id"]), intval($artist_id[5]["id"]),
		intval($artist_id[6]["id"]), intval($artist_id[7]["id"]), intval($artist_id[8]["id"]),
		intval($artist_id[9]["id"]),
		intval($genre_tag_id[0]["id"]), intval($genre_tag_id[1]["id"]), intval($genre_tag_id[2]["id"]),
		intval($genre_tag_id[3]["id"]), intval($genre_tag_id[4]["id"]), intval($genre_tag_id[5]["id"]),
		intval($genre_tag_id[6]["id"]), intval($genre_tag_id[7]["id"]), intval($genre_tag_id[8]["id"]),
		intval($genre_tag_id[9]["id"])
		);
		#var_dump($query1);
		$query2 = sprintf("0 %f %f %f %f %f %f %f %f %f %f %f %f %f %f %f %f %f %f %f %f %f %f
		%d %d 
		%d %d %d %d %d %d %d %d %d %d 
		%d %d %d %d %d %d %d %d %d %d",
		$stable_duration_from, $stable_duration_to, 
		$stable_percent_from, $stable_percent_to, 
		$run_percent_from, $run_percent_to, 
		$rate_from, $rate_to, 
		$meter_from, $meter_to, 
		$max_tempo_drift_from, $max_tempo_drift_to, 
		$max_percent_deviation_from, $max_percent_deviation_to, 
		$max_successive_change_from, $max_successive_change_to,
		$mismatch_from, $mismatch_to,
		$loudness_from, $loudness_to, 
		$danceability_from, $danceability_to, 
		$year_from, $year_to,

		intval($artist_id[0]), intval($artist_id[1]), intval($artist_id[2]),
		intval($artist_id[3]), intval($artist_id[4]), intval($artist_id[5]),
		intval($artist_id[6]), intval($artist_id[7]), intval($artist_id[8]),
		intval($artist_id[9]),
		intval($lang_tag_id[0]), intval($lang_tag_id[1]), intval($lang_tag_id[2]),
		intval($lang_tag_id[3]), intval($lang_tag_id[4]), intval($lang_tag_id[5]),
		intval($lang_tag_id[6]), intval($lang_tag_id[7]), intval($lang_tag_id[8]),
		intval($lang_tag_id[9])
	);

		// filter the result with api
		$filter_id1=MusicRxAPI::search_filter($query1);
		//var_dump(count($filter_id1));
		$filter_id2=MusicRxAPI::search_filter($query2);
		//var_dump(count($filter_id2));
		#Jiakun: $filter_id2 includes $filter_id1?
		#$filter_id=array_merge($filter_id1,$filter_id2);
		$filter_id = $filter_id1 + $filter_id2;
		
		//var_dump(count($filter_id));
		// if the number of tracks is too small, then change and filter again
		$final_list=array_merge($song_id,$filter_id);
		//var_dump($final_list);
		//var_dump($song_id);
		if(count($song_id)>=5){
				$track_title=array_slice($song_id,0,5);
		}
		else{
				$track_title=$song_id;
		}
		if(count($filter_id)<=10-count($track_title))
			$track_other=$filter_id;
		else{
			$track_other=array_slice($final_list,count($song_id),10-count($track_title));
		}
		$echonest_id=array_merge($track_title,$track_other);
		//var_dump($echonest_id);
		if(count($final_list)<200){
			$artist_id = array();
		// Query
			$query1 = sprintf("0 %f %f %f %f %f %f %f %f %f %f %f %f %f %f %f %f %f %f %f %f %f %f
				%d %d 
				%d %d %d %d %d %d %d %d %d %d 
				%d %d %d %d %d %d %d %d %d %d",
			$stable_duration_from, $stable_duration_to, 
			$stable_percent_from, $stable_percent_to, 
			$run_percent_from, $run_percent_to, 
			$rate_from, $rate_to, 
			$meter_from, $meter_to, 
			$max_tempo_drift_from, $max_tempo_drift_to, 
			$max_percent_deviation_from, $max_percent_deviation_to, 
			$max_successive_change_from, $max_successive_change_to,
			$mismatch_from, $mismatch_to,
			$loudness_from, $loudness_to, 
			$danceability_from, $danceability_to, 
			$year_from, $year_to,
			intval($artist_id[0]), intval($artist_id[1]), intval($artist_id[2]),
			intval($artist_id[3]), intval($artist_id[4]), intval($artist_id[5]),
			intval($artist_id[6]), intval($artist_id[7]), intval($artist_id[8]),
			intval($artist_id[9]),
			intval($genre_tag_id[0]), intval($genre_tag_id[1]), intval($genre_tag_id[2]),
			intval($genre_tag_id[3]), intval($genre_tag_id[4]), intval($genre_tag_id[5]),
			intval($genre_tag_id[6]), intval($genre_tag_id[7]), intval($genre_tag_id[8]),
			intval($genre_tag_id[9])
			);
	
			$query2 = sprintf("0 %f %f %f %f %f %f %f %f %f %f %f %f %f %f %f %f %f %f %f %f %f %f
			%d %d 
			%d %d %d %d %d %d %d %d %d %d 
			%d %d %d %d %d %d %d %d %d %d",
			$stable_duration_from, $stable_duration_to, 
			$stable_percent_from, $stable_percent_to, 
			$run_percent_from, $run_percent_to, 
			$rate_from, $rate_to, 
			$meter_from, $meter_to, 
			$max_tempo_drift_from, $max_tempo_drift_to, 
			$max_percent_deviation_from, $max_percent_deviation_to, 
			$max_successive_change_from, $max_successive_change_to,
			$mismatch_from, $mismatch_to,
			$loudness_from, $loudness_to, 
			$danceability_from, $danceability_to, 
			$year_from, $year_to,
			intval($artist_id[0]), intval($artist_id[1]), intval($artist_id[2]),
			intval($artist_id[3]), intval($artist_id[4]), intval($artist_id[5]),
			intval($artist_id[6]), intval($artist_id[7]), intval($artist_id[8]),
			intval($artist_id[9]),
			intval($lang_tag_id[0]), intval($lang_tag_id[1]), intval($lang_tag_id[2]),
			intval($lang_tag_id[3]), intval($lang_tag_id[4]), intval($lang_tag_id[5]),
			intval($lang_tag_id[6]), intval($lang_tag_id[7]), intval($lang_tag_id[8]),
			intval($lang_tag_id[9])
			);
			//api search

			$filter_id1=MusicRxAPI::search_filter($query1);
			$filter_id2=MusicRxAPI::search_filter($query2);
			//$filter_id=array_merge($filter_id1,$filter_id2);
			$filter_id=$filter_id1+$filter_id2;
			
			if(count($echonest_id)<10)
				$cou=count($final_list);
			// if the number of tracks is too small, then change and filter again
			$final_list=array_merge($final_list,$filter_id);
			if(count($echonest_id)<10)
				$echonest_id=array_merge($echonest_id,array_slice($final_list,$cou,10-count($echonest_id)));
			//var_dump($filter_list);
			$filter_result = fopen("/home/jiakun/exercise/search/db/".$student[0]['id'].".db", "w") or die("Unable to open file!");
			$final_list_array = array();
			//var_dump(count($final_list));
			foreach($final_list as $id){
				array_push($final_list_array, $id['song_id']);
			}
			//var_dump($final_list_array);
			$final_list_conn=MusicRxAPI::join_conn($final_list_array);
			
			$final_song_indexs=DB::sql("SELECT song_index FROM previews WHERE song_id IN $final_list_conn");
			//var_dump($final_song_indexs);
			foreach($final_song_indexs as $id){
				fwrite($filter_result, $id['song_index']."\r\n");
			}
			fclose($filter_result);
			}
		else{
			// write the file
			// search the track_id and song_id   decide the position of the file ?????????
			$filter_result = fopen("/home/jiakun/exercise/search/db/".$student[0]['id'].".db", "w") or die("Unable to open file!");
			//Jiakun: Save song_index
			//
			$final_list_array = array();
			foreach($final_list as $id){
				
				array_push($final_list_array, $id['song_id']);
			}
			//var_dump(count($final_list_array));
			$final_list_conn=MusicRxAPI::join_conn($final_list_array);
			
			// inconsistency exists in db (same song_id)
			$final_song_indexs=DB::sql("SELECT song_index FROM previews WHERE song_id IN $final_list_conn");
			//var_dump(count($final_song_indexs));
			foreach($final_song_indexs as $id){
				fwrite($filter_result, $id['song_index']."\r\n");
			}
			fclose($filter_result);
		}
		//var_dump($echonest_id);
		//$temp=array();
		//foreach($echonest_id as $id){
		//array_push($temp,'"'.$id['song_id'].'"');
		//}
		$tend = $date->format('U = Y-m-d H:i:s') . "\n";
		
		$runtime_id = fopen("/home/jiakun/exercise/search/runtime/".$s_id.".txt", "a") or die("Unable to open file!");
		fwrite($runtime_id, $tstart.$tend."\n");
		fclose($runtime_id);
		
		return $echonest_id;
	}
	function student_recommend($s_id){
		// read from the DB
		$date = new DateTime();
		$t1 = $date->format('U = Y-m-d H:i:s') . "\n";
		
		//$student=DB::sql("select * from students where id='$s_id'");
		$history=DB::sql("SELECT track,suitable FROM student_tracks where student_id='$s_id'");
		var_dump($s_id);
		//var_dump(count($history));
		$history_id = fopen("/home/jiakun/exercise/search/hist/".$s_id.".his", "w") or die("Unable to open file!");
		$echonest_id_array = array();
		foreach($history as $id){
			array_push($echonest_id_array, $id['track']);
		}
		$echonest_id_conn=MusicRxAPI::join_conn($echonest_id_array);
		$song_index_array=DB::sql("SELECT song_index FROM previews WHERE echonest_id IN $echonest_id_conn");
		$num_history = count($song_index_array);
		//var_dump($num_history);
		for($i = 0; $i < $num_history; $i++){
			fwrite($history_id, $song_index_array[$i]['song_index']." ".$history[$i]['suitable']."\r\n");
		}
		fclose($history_id);
		
		$date = new DateTime();
		$t2 = $date->format('U = Y-m-d H:i:s') . "\n";
		

		$result = array();
		$final_result = array();
		//
		// R recommendation programming
		//
		//$algorithm = "bandit_cf_gibbs";
		$algorithm = "bandit_cf_filter";
		$R_PATH = "/home/jiakun/MusicRx/xingzhe/Bayes/R";
        $cmd = sprintf("Rscript %s/%s.R  %s", $R_PATH, $algorithm, $s_id);
        error_log($cmd);        
		$cmd_result = shell_exec($cmd);
		$cmd_result = substr($cmd_result, 0, -1);	//delete redundant ' ' at the end of str
		$date = new DateTime();
		$t3 = $date->format('U = Y-m-d H:i:s') . "\n";
		//var_dump($t3);
		$pre_result = MusicRxAPI::_parse_payload($cmd_result);
		$final_result = array();
		foreach($pre_result as $song_index){
			$result = DB::sql("SELECT song_id FROM previews WHERE song_index ='$song_index'");
			$song_id=$result[0]['song_id'];
			array_push($final_result,$song_id);
		}	
		$date = new DateTime();
		$t4 = $date->format('U = Y-m-d H:i:s') . "\n";
		//var_dump($t4);
		//var_dump(count($pre_result));
		MusicRxAPI::save_runtime($t1,$t2,$t3,$t4,$s_id);
		return $final_result;
	}
	function save_runtime($t1,$t2,$t3,$t4,$s_id){
		$runtime_id = fopen("/home/jiakun/exercise/search/runtime/".$s_id.".txt", "a") or die("Unable to open file!");
		fwrite($runtime_id, $t1.$t2.$t3.$t4."\n");
		fclose($runtime_id);
	}
	
	
	
	
	
	
	
	
	
		
	function filter($p_id){
		$track_title=array();
		$track_other=array();
		// read the assessment from database
		$patient=DB::sql("select * from patients where id='$p_id'");
		//var_dump($patient);
		$language=$patient[0]['language'];
		//var_dump($language);
		$Mandarin=array("china","chinese","mandarin","cantonese");
		$Malay=array("malaysia","malay");
		$Tamil=array("tamil");
		$lang_tag=array();
		if($language=="Mandarin")
			$lang_tag=$Mandarin;
		else if($language=="Malay")
			$lang_tag=$Malay;
		else $lang_tag="";
		//var_dump($lang_tag);
		if($lang_tag==""){
			$lang_tag_id=array();
		}
		else{
			$lang_tag_conn=MusicRxAPI::connection($lang_tag);
			$lang_tag_id=DB::sql("SELECT id FROM tags WHERE tag regexp '$lang_tag_conn' order by number DESC"); //sort by number
		}
		//var_dump($lang_tag_conn);
		//var_dump($lang_tag_id);

		// find the corresponding id for the artist
		$favourite=$patient[0]['MBP_2']; //artist, song, composer
		if($favourite==""){
			$artist_id=array();
			$song_id1=array();
		}
		else{
			$artist=MusicRxAPI::split_refine($favourite,",");
			$artist_conn=MusicRxAPI::join_conn($artist);
			$artist_id=DB::sql("SELECT id FROM artists WHERE lower(name) IN $artist_conn");
			//var_dump(intval($artist_id[0]["id"]));
			// find the corresponding id for the song
			$song_title=$artist;
			//var_dump($song_title);
			$song_conn=MusicRxAPI::join_conn($song_title);
			//var_dump($song_conn);
			$song_id1=DB::sql("SELECT song_id FROM previews WHERE lower(track_name) IN $song_conn");
		}
		//var_dump($song_id1);
	
		// find the corresponding id for the tags
		//classical
		$genre_tag=array();
		if($patient[0]['MBP_2a']!=NULL){
			array_push($genre_tag,"classic");
		}
		//pop
		if($patient[0]['MBP_2b']!=NULL||$patient[0]['MBP_2c']!=NULL){
			array_push($genre_tag,"pop");
		}
		//var_dump($genre_tag);
		// find the corresponding id for the genre_tag
		if(count($genre_tag)==0){
			$genre_tag_id=array();
		}
		else{
			$genre_conn=MusicRxAPI::connection($genre_tag);
			$genre_tag_id=DB::sql("SELECT id FROM tags WHERE tag regexp '$genre_conn' and number>500 order by number DESC"); //sort by number
		}
	//$duaration="";
		//var_dump($genre_tag_id[0]["id"]);

		$tempo=$patient[0]['tempo_range'];
		$tempo=MusicRxAPI::split_refine($tempo,"-");
		$min_tempo=intval($tempo[0]);
		$max_tempo=intval($tempo[1]);
		if($min_tempo!=0){
			$rate_from=$min_tempo;
		}
		if($max_tempo!=0){
			$rate_to=$max_tempo;
		}
	//var_dump($rate_from);
	//var_dump($rate_to);

		$recommendation=$patient[0]['general_recommendations'];

	//title
		$list_songs=$patient[0]['list_songs'];
		if($list_songs==""){
			$song_id2=array();
		}
		else{
			$list_songs=MusicRxAPI::split_refine($list_songs,",");
			//var_dump($list_songs);
			$song_conn=MusicRxAPI::join_conn($list_songs);
			//var_dump($song_conn);
			$song_id2=DB::sql("SELECT song_id FROM previews WHERE lower(track_name) IN $song_conn");
		}
		//var_dump($song_id2);
		$song_id=array();
		$song_id=array_merge($song_id1,$song_id2);
	//var_dump($song_id);

		$stable_duration_from=60; $stable_duration_to=2400; 
		$stable_percent_from=80; $stable_percent_to=100; 
		$run_percent_from=66.77; $run_percent_to=100; 
		$rate_from=50; $rate_to=175; 
		$meter_from=3; $meter_to=4; 
		$max_tempo_drift_from=0; $max_tempo_drift_to=5; 
		$max_percent_deviation_from=0.01; $max_percent_deviation_to=5; 
		$max_successive_change_from=0; $max_successive_change_to=5;
		$mismatch_from=-24.63; $mismatch_to=55.28;
		$loudness_from=-100; $loudness_to=100; 
		$danceability_from=0; $danceability_to=1; 
		$year_from=1800; $year_to=2013;
		$key="stable_duration";

		//var_dump($artist);
	// Query
		$query1 = sprintf("0 %f %f %f %f %f %f %f %f %f %f %f %f %f %f %f %f %f %f %f %f %f %f
		%d %d 
		%d %d %d %d %d %d %d %d %d %d 
		%d %d %d %d %d %d %d %d %d %d",
		$stable_duration_from, $stable_duration_to, 
		$stable_percent_from, $stable_percent_to, 
		$run_percent_from, $run_percent_to, 
		$rate_from, $rate_to, 
		$meter_from, $meter_to, 
		$max_tempo_drift_from, $max_tempo_drift_to, 
		$max_percent_deviation_from, $max_percent_deviation_to, 
		$max_successive_change_from, $max_successive_change_to,
		$mismatch_from, $mismatch_to,
		$loudness_from, $loudness_to, 
		$danceability_from, $danceability_to, 
		$year_from, $year_to,

		intval($artist_id[0]["id"]), intval($artist_id[1]["id"]), intval($artist_id[2]["id"]),
		intval($artist_id[3]["id"]), intval($artist_id[4]["id"]), intval($artist_id[5]["id"]),
		intval($artist_id[6]["id"]), intval($artist_id[7]["id"]), intval($artist_id[8]["id"]),
		intval($artist_id[9]["id"]),
		intval($genre_tag_id[0]["id"]), intval($genre_tag_id[1]["id"]), intval($genre_tag_id[2]["id"]),
		intval($genre_tag_id[3]["id"]), intval($genre_tag_id[4]["id"]), intval($genre_tag_id[5]["id"]),
		intval($genre_tag_id[6]["id"]), intval($genre_tag_id[7]["id"]), intval($genre_tag_id[8]["id"]),
		intval($genre_tag_id[9]["id"])
		);
		#var_dump($query1);
		$query2 = sprintf("0 %f %f %f %f %f %f %f %f %f %f %f %f %f %f %f %f %f %f %f %f %f %f
		%d %d 
		%d %d %d %d %d %d %d %d %d %d 
		%d %d %d %d %d %d %d %d %d %d",
		$stable_duration_from, $stable_duration_to, 
		$stable_percent_from, $stable_percent_to, 
		$run_percent_from, $run_percent_to, 
		$rate_from, $rate_to, 
		$meter_from, $meter_to, 
		$max_tempo_drift_from, $max_tempo_drift_to, 
		$max_percent_deviation_from, $max_percent_deviation_to, 
		$max_successive_change_from, $max_successive_change_to,
		$mismatch_from, $mismatch_to,
		$loudness_from, $loudness_to, 
		$danceability_from, $danceability_to, 
		$year_from, $year_to,

		intval($artist_id[0]), intval($artist_id[1]), intval($artist_id[2]),
		intval($artist_id[3]), intval($artist_id[4]), intval($artist_id[5]),
		intval($artist_id[6]), intval($artist_id[7]), intval($artist_id[8]),
		intval($artist_id[9]),
		intval($lang_tag_id[0]), intval($lang_tag_id[1]), intval($lang_tag_id[2]),
		intval($lang_tag_id[3]), intval($lang_tag_id[4]), intval($lang_tag_id[5]),
		intval($lang_tag_id[6]), intval($lang_tag_id[7]), intval($lang_tag_id[8]),
		intval($lang_tag_id[9])
	);

		// filter the result with api
		$filter_id1=MusicRxAPI::search_filter($query1);
		var_dump(count($filter_id1));
		$filter_id2=MusicRxAPI::search_filter($query2);
		var_dump(count($filter_id2));
		#Jiakun: $filter_id2 includes $filter_id1?
		#$filter_id=array_merge($filter_id1,$filter_id2);
		$filter_id = $filter_id1 + $filter_id2;
		
		var_dump(count($filter_id));
		// if the number of tracks is too small, then change and filter again
		$final_list=array_merge($song_id,$filter_id);
		//var_dump($final_list);
		//var_dump($song_id);
		if(count($song_id)>=5){
				$track_title=array_slice($song_id,0,5);
		}
		else{
				$track_title=$song_id;
		}
		if(count($filter_id)<=10-count($track_title))
			$track_other=$filter_id;
		else{
			$track_other=array_slice($final_list,count($song_id),10-count($track_title));
		}
		$echonest_id=array_merge($track_title,$track_other);
		//var_dump($echonest_id);
		if(count($final_list)<200){
			$artist_id = array();
		// Query
			$query1 = sprintf("0 %f %f %f %f %f %f %f %f %f %f %f %f %f %f %f %f %f %f %f %f %f %f
				%d %d 
				%d %d %d %d %d %d %d %d %d %d 
				%d %d %d %d %d %d %d %d %d %d",
			$stable_duration_from, $stable_duration_to, 
			$stable_percent_from, $stable_percent_to, 
			$run_percent_from, $run_percent_to, 
			$rate_from, $rate_to, 
			$meter_from, $meter_to, 
			$max_tempo_drift_from, $max_tempo_drift_to, 
			$max_percent_deviation_from, $max_percent_deviation_to, 
			$max_successive_change_from, $max_successive_change_to,
			$mismatch_from, $mismatch_to,
			$loudness_from, $loudness_to, 
			$danceability_from, $danceability_to, 
			$year_from, $year_to,
			intval($artist_id[0]), intval($artist_id[1]), intval($artist_id[2]),
			intval($artist_id[3]), intval($artist_id[4]), intval($artist_id[5]),
			intval($artist_id[6]), intval($artist_id[7]), intval($artist_id[8]),
			intval($artist_id[9]),
			intval($genre_tag_id[0]), intval($genre_tag_id[1]), intval($genre_tag_id[2]),
			intval($genre_tag_id[3]), intval($genre_tag_id[4]), intval($genre_tag_id[5]),
			intval($genre_tag_id[6]), intval($genre_tag_id[7]), intval($genre_tag_id[8]),
			intval($genre_tag_id[9])
			);
	
			$query2 = sprintf("0 %f %f %f %f %f %f %f %f %f %f %f %f %f %f %f %f %f %f %f %f %f %f
			%d %d 
			%d %d %d %d %d %d %d %d %d %d 
			%d %d %d %d %d %d %d %d %d %d",
			$stable_duration_from, $stable_duration_to, 
			$stable_percent_from, $stable_percent_to, 
			$run_percent_from, $run_percent_to, 
			$rate_from, $rate_to, 
			$meter_from, $meter_to, 
			$max_tempo_drift_from, $max_tempo_drift_to, 
			$max_percent_deviation_from, $max_percent_deviation_to, 
			$max_successive_change_from, $max_successive_change_to,
			$mismatch_from, $mismatch_to,
			$loudness_from, $loudness_to, 
			$danceability_from, $danceability_to, 
			$year_from, $year_to,
			intval($artist_id[0]), intval($artist_id[1]), intval($artist_id[2]),
			intval($artist_id[3]), intval($artist_id[4]), intval($artist_id[5]),
			intval($artist_id[6]), intval($artist_id[7]), intval($artist_id[8]),
			intval($artist_id[9]),
			intval($lang_tag_id[0]), intval($lang_tag_id[1]), intval($lang_tag_id[2]),
			intval($lang_tag_id[3]), intval($lang_tag_id[4]), intval($lang_tag_id[5]),
			intval($lang_tag_id[6]), intval($lang_tag_id[7]), intval($lang_tag_id[8]),
			intval($lang_tag_id[9])
			);
			//api search
			$filter_id1=MusicRxAPI::search_filter($query1);
			$filter_id2=MusicRxAPI::search_filter($query2);
			$filter_id=array_merge($filter_id1,$filter_id2);
			
			if(count($echonest_id)<10)
				$cou=count($final_list);
			// if the number of tracks is too small, then change and filter again
			$final_list=array_merge($final_list,$filter_id);
			if(count($echonest_id)<10)
				$echonest_id=array_merge($echonest_id,array_slice($final_list,$cou,10-count($echonest_id)));
			//var_dump($filter_list);
			$filter_result = fopen("/home/ubuntu/storage/BEATS/search/db/".$patient[0]['id'].".db", "w") or die("Unable to open file!");
			foreach($final_list as $id){
				fwrite($filter_result, $id['song_id']."\r\n");
			}
			fclose($filter_result);
			}
		else{
			// write the file
			// search the track_id and song_id   decide the position of the file ?????????
			$filter_result = fopen("/home/ubuntu/storage/BEATS/search/db/".$patient[0]['id'].".db", "w") or die("Unable to open file!");
			//Jiakun: Save song_index
			foreach($final_list as $id){
				$song_id_str =  $id['song_id'];
				$final_song_index = DB::sql("SELECT song_index FROM previews WHERE song_id = '$song_id_str'");
				//var_dump($final_song_index[0]);
				//fwrite($filter_result, $id['song_id']."\r\n");
				fwrite($filter_result, $final_song_index[0]['song_index']."\r\n");
			}
			fclose($filter_result);
		}
		//var_dump($echonest_id);
		echo($echonest_id);
		//$temp=array();
		//foreach($echonest_id as $id){
		//array_push($temp,'"'.$id['song_id'].'"');
		//}
		return $echonest_id;
	}
	
	function recommend($p_id){
		// read from the DB
		$date = new DateTime();
		$t1 = $date->format('U = Y-m-d H:i:s') . "\n";
		
		$patient=DB::sql("select * from patients where id='$p_id'");
		$history=DB::sql("SELECT track,suitable FROM tracks where patient_id='$p_id'");
		//var_dump($history);
		$history_id = fopen("/home/ubuntu/storage/BEATS/search/hist/".$patient[0]['id'].".his", "w") or die("Unable to open file!");
		//foreach($history as $id){
			//$str=$id['track'];
			//var_dump($id['track']);
			//search for the track_id
			//$result=DB::sql("SELECT song_id FROM previews WHERE echonest_id='$str'");			
			//JIAKUN:
			//$result=DB::sql("SELECT song_index FROM previews WHERE echonest_id = '$str'");
			//$str=$result[0]['song_index'];
			//if((int)$str < 20000 && (int)$str > 0){
			//fwrite($history_id, $str." ".$id['suitable']."\r\n");
			//}
		$echonest_id_array = array();
		foreach($history as $id){
			array_push($echonest_id_array, $id['track']);
		}
		$echonest_id_conn=MusicRxAPI::join_conn($echonest_id_array);
		$song_index_array=DB::sql("SELECT song_index FROM previews WHERE echonest_id IN $echonest_id_conn");
		//foreach($song_index_array as $id){
		//	$str=$id['song_index'];
		//	fwrite($history_id, $str." ".$id['suitable']."\r\n");
		//}
		$num_history = count($song_index_array);
		//var_dump($num_history);
		for($i = 0; $i < $num_history; $i++){
			fwrite($history_id, $song_index_array[$i]['song_index']." ".$history[$i]['suitable']."\r\n");
		}
		fclose($history_id);
		
		$date = new DateTime();
		$t2 = $date->format('U = Y-m-d H:i:s') . "\n";
		

		$result = array();
		$final_result = array();
		//
		// R recommendation programming
		//
		//$algorithm = "bandit_cf_gibbs";
		$algorithm = "bandit_cf_filter";
		$R_PATH = "/home/jiakun/MusicRx/xingzhe/Bayes/R";
        $cmd = sprintf("Rscript %s/%s.R  %s", $R_PATH, $algorithm, $patient[0]['id']);
        error_log($cmd);        
		$cmd_result = shell_exec($cmd);
		$cmd_result = substr($cmd_result, 0, -1);	//delete redundant ' ' at the end of str
		$date = new DateTime();
		$t3 = $date->format('U = Y-m-d H:i:s') . "\n";
		
		//var_dump($cmd_result);
		$pre_result = MusicRxAPI::_parse_payload($cmd_result);
		//var_dump($pre_result);
		//$pre_result_conn = MusicRxAPI::join_conn($pre_result);
		//var_dump($pre_result_conn);
		//$final_result_db=DB::sql("SELECT song_id FROM previews WHERE song_index IN $pre_result_conn");
		$final_result = array();
		foreach($pre_result as $song_index){
			$result = DB::sql("SELECT song_id FROM previews WHERE song_index ='$song_index'");
			$song_id=$result[0]['song_id'];
			array_push($final_result,$song_id);
		}	
		//$final_result = array();
		//var_dump($final_result_db[0]);
		//for($i = 0; $i < 10; $i++){
			//array_push($final_result,$final_result_db[i]);
		//}
	
		$date = new DateTime();
		$t4 = $date->format('U = Y-m-d H:i:s') . "\n";
		
		//var_dump(count($pre_result));
		return $final_result;				
		//return $final_result;
		
		//
		// C recommendation programming
		//
		/*
		$redis = new Predis\Client();
		$query= $patient[0]['id'].".db ".$patient[0]['id'].'.his';
		$channel = md5($query);
		//$cached = false;
		//$result = apc_fetch($channel, $cached);
		$redis->lpush('recommend', "$channel $query");
		$pubsub = $redis->pubSub();
		$pubsub->subscribe($channel);
		$result = array();
		foreach ($pubsub as $message) {
			switch ($message->kind) {
			case 'message':
				if ($message->channel == $channel) {
					if ($message->payload != '') {
						$final_result = MusicRxAPI::_parse_payload($message->payload);
						$pubsub->unsubscribe();
					}	
				} 
				break;
			}	
		}	
		unset($pubsub);
		
		//apc_store($channel, $result, 3600);
		return $final_result;
		*/
	}
		
	function search_filter($query){
		$redis = new Predis\Client();
		$channel = md5($query);
		$cached = false;
		$result = apc_fetch($channel, $cached);
		if(!$cached){
			$redis->lpush('musicrx', "$channel $query");
			$pubsub = $redis->pubSub();
			$pubsub->subscribe($channel);
			$result = array();
			foreach ($pubsub as $message) {
				switch ($message->kind) {
				case 'message':
					if ($message->channel == $channel) {
						if ($message->payload != '') {
							$result = MusicRxAPI::_parse_search_payload($message->payload);
							$pubsub->unsubscribe();
						}
					}
					break;
				}
			}
			unset($pubsub);
			apc_store($channel, $result, 3600);
		}
		$ids = array();
		foreach($result['points'] as $point){
			$ids[] = intval($point[0]);
		}
		$tracks = DB::sql(sprintf("SELECT song_id FROM previews WHERE id IN (0,%s)", join(',', $ids)));
		return $tracks;
	}

	function _parse_payload($str){
		return explode(" ",$str); 
	}
	
	function _parse_search_payload($str){
		$keyID = array(
			'stable_duration' => 'Stable Duration (s)',
			'stable_percent' => 'Stable %',
			'run_percent' => 'Run %',
			'rate' => 'Est. Tempo (BPM)',
			'meter' => 'Est. Meter',
			'max_tempo_drift' => 'Max. % Tempo Drift',
			'max_percent_deviation' => 'Max. % Deviation from Location',
			'max_successive_change' => 'Max. Successive % Change',
			'mismatch' => 'Est. Tempo Mismatch (%)',
		);
		list($key, $stats, $bins, $points, $portion) = explode('$', $str);
		list($min, $max, $bin) = explode(',', $stats);
		list($total, $selected) = explode(',', $portion);
		$bins = explode(',', trim($bins, ', '));
		$points = explode(',', trim($points, ', '));
		for($i = 0; $i < count($bins); $i+=2){
			$b[] = array($bins[$i], $bins[$i+1]);
		}
		for($i = 0; $i < count($points); $i+=2){
			$p[] = array($points[$i], $points[$i+1]);
		}
		$names = array_keys($keyID);
		$labels = array_values($keyID);
		return array(
			'key' => $names[$key],
			'label' => $labels[$key],
			'total' => intval($total),
			'selected' => intval($selected),
			'min' => floatval($min),
			'max' => floatval($max),
			'bin' => floatval($bin),
			'bins' => $b,
			'points' => $p,
		);
	}

	function split_refine($str,$sp){
		$split_str=explode($sp,$str);
		$num = count($split_str); 
		for($i=0;$i<$num;++$i){
			$split_str[$i]=trim(strtolower($split_str[$i]));
		}
		return $split_str;
	}

	function connection($name){
		return	join('|',$name);
	}

	function join_conn($artist){
		$new_artist=array();
		foreach($artist as $t){
			array_push($new_artist,'"'.$t.'"');
		}
		return '('.join(',',$new_artist).')';
	}

}
?>
