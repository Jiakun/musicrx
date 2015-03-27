<?php

class MusicRxPatient{

	function management(){
		
		$name=$_POST['exist_patient'];
		$len=strlen($name);

		//var_dump(strlen($exist_therapist));
		if ($name[$len-1]=='1'){
			$this->read_assessment(substr($name,0,$len-1));
		}
		else if ($name[$len-1]=='2'){
			$this->patient_playlist(substr($name,0,$len-1));
		}
		else{
			$this->remove_patient(substr($name,0,$len-1));
		}
	}
	function save_student_register(){
		$name = $_POST['name'];
		$system_type = $_POST['system'];
		if($system_type == 1){
		// System A: Old system
		// save s_id to student DB
			$student=new Axon('students');
			$student->name=$name;
			$student->save();
			$s_ids=DB::sql("SELECT id FROM students WHERE name='$name'");
			$s_id=$s_ids[0]['id'];
			$this->student_oldsystem_playlist($s_id);
		}
		else if($system_type == 2){
		// System B: New system
			F3::set('name', $name);
			MusicRxHome::studentassessment();
		}
		else if($system_type == 3){
		// System C: Group system
			F3::set('name', $name);
			MusicRxHome::group_assessment();
		}
	}
	function student_management(){
		
		$name=$_POST['exist_student'];
		$len=strlen($name);

		//var_dump(strlen($exist_therapist));
		if ($name[$len-1]=='1'){
			$this->read_assessment(substr($name,0,$len-1));
		}
		else if ($name[$len-1]=='2'){
			$this->student_playlist(substr($name,0,$len-1));
		}
		else{
			$this->remove_patient(substr($name,0,$len-1));
		}
	}
	
	function read_assessment($patient_name){	
		// read by the patient name or id and show the saved assessment html
		//var_dump($patient_name);
		//read from the database
		// if empty else read and show into the page
		if(isset($patient_name)){
			$patient_info=DB::sql("select * from patients where `name`='$patient_name'");
			//var_dump($patient_info);
			//var_dump($patient_info[0]["name"]);
			$patient=array(
				'id' => $patient_info[0]["id"],	'name'=> $patient_info[0]["name"],'therapist_id' => $patient_info[0]["therapist_id"],
				'language' => $patient_info[0]["language"],	'specify_dialects' => $patient_info[0]["specify_dialects"],	'specify_others' => $patient_info[0]["specify_others"],	'ward' => $patient_info[0]["ward"],	'bed' => $patient_info[0]["bed"],	'referral_reason' => $patient_info[0]["referral_reason"],	'mobility' => $patient_info[0]["mobility"],	'diagnosis' => $patient_info[0]["diagnosis"],	'medical_history' => $patient_info[0]["medical_history"],	'social_history' => $patient_info[0]["social_history"],	'PSTI_1_1' => $patient_info[0]["PSTI_1_1"],	'PSTI_1_2' => $patient_info[0]["PSTI_1_2"],	'PSTI_2_1' => $patient_info[0]["PSTI_2_1"],	'PSTI_2_2' => $patient_info[0]["PSTI_2_2"],	'PSTI_3' => $patient_info[0]["PSTI_3"],	'PSTI_4' => $patient_info[0]["PSTI_4"],	'POB_1' => $patient_info[0]["POB_1"],	'POB_2' => $patient_info[0]["POB_2"],	'specify_POB_2' => $patient_info[0]["specify_POB_2"],	'POB_3' => $patient_info[0]["POB_3"],	'POB_4' => $patient_info[0]["POB_4"],	'specify_POB_4' => $patient_info[0]["specify_POB_4"],	'POB_5a' => $patient_info[0]["POB_5a"],	'specify_POB_5a' => $patient_info[0]["specify_POB_5a"],	'POB_5b' => $patient_info[0]["POB_5b"],	'specify_POB_5b' => $patient_info[0]["specify_POB_5b"],	'POB_5c' => $patient_info[0]["POB_5c"],	'specify_POB_5c' => $patient_info[0]["specify_POB_5c"],	'POB_6a' => $patient_info[0]["POB_6a"],	'POB_6b' => $patient_info[0]["POB_6b"],	'POB_7' => $patient_info[0]["POB_7"],	'POB_8' => $patient_info[0]["POB_8"],	'POB_9' => $patient_info[0]["POB_9"],	'POB_10_1' => $patient_info[0]["POB_10_1"],	'POB_10_2' => $patient_info[0]["POB_10_2"],	'specify_POB_10_2' => $patient_info[0]["specify_POB_10_2"],	'POB_11_1' => $patient_info[0]["POB_11_1"],	'POB_11_2' => $patient_info[0]["POB_11_2"],	'POB_12_1' => $patient_info[0]["POB_12_1"],	'POB_12_2' => $patient_info[0]["POB_12_2"],	'specify_POB_11_2' => $patient_info[0]["specify_POB_11_2"],	'POB_13' => $patient_info[0]["POB_13"],	'POB_14' => $patient_info[0]["POB_14"],	'specify_POB_14' => $patient_info[0]["specify_POB_14"],	'POB_15' => $patient_info[0]["POB_15"],	'MBP_1' => $patient_info[0]["MBP_1"],	'MBP_2' => $patient_info[0]["MBP_2"],	'MBP_2a' => $patient_info[0]["MBP_2a"],	'MBP_2b' => $patient_info[0]["MBP_2b"],	'specify_MBP_2b' => $patient_info[0]["specify_MBP_2b"],	'MBP_2c' => $patient_info[0]["MBP_2c"],	'specify_MBP_2c' => $patient_info[0]["specify_MBP_2c"],	'specify_MBP_2d' => $patient_info[0]["specify_MBP_2d"],	'MBP_3_1' => $patient_info[0]["MBP_3_1"],	'MBP_3_2' => $patient_info[0]["MBP_3_2"],	'general_recommendations' => $patient_info[0]["general_recommendations"],	'therapy_type' => $patient_info[0]["therapy_type"],	'NMT_protocol' => $patient_info[0]["NMT_protocol"],	'time_signature' => $patient_info[0]["time_signature"],	'tempo_range' => $patient_info[0]["tempo_range"],	'list_songs' => $patient_info[0]["list_songs"],	'assessor' => $patient_info[0]["assessor"],'date' => $patient_info[0]["date"],'specify_POB_12_2' =>$patient_info[0]["specify_POB_12_2"],
			);
			F3::set('patient', $patient);
			F3::set('page', array('patient_assessment', ''));
			echo Template::serve('patient_assessment.html');
		}
		else{
		F3::set('page', array('patient_assessment', ''));
		echo Template::serve('patient_assessment.html');
		}
	}

	function write_student_assessment(){
		$date = new DateTime();
		$questionnaire_end_time = $date->format('U = Y-m-d H:i:s') . "\n";

		
		$name=$_POST['name']; $age=$_POST['age']; $session_30min=$_POST['session_30min']; $session_60min=$_POST['session_60min']; $exercisetime=$_POST['exercisetime']; $song=$_POST['song'];$artist=$_POST['artist']; $genre=$_POST['genre'];
		$loudness=$_POST['loudness']; $energy=$_POST['energy']; $happiness=$_POST['happiness']; $speed=$_POST['speed']; $familiar=$_POST['familiar'];
		//create a new student instance
		$student=new Axon('students');
		$student->name=$name;
		$student->age=$age; $student->session_30min=$session_30min; $student->session_60min=$session_60min; $student->exercisetime=$exercisetime; $student->song=$song;$student->artist=$artist; $student->genre=$genre;
		$student->loudness=$loudness; $student->energy=$energy; $student->happiness=$happiness; $student->speed=$speed; $student->familiar=$familiar; 
		//?
		$student->filter=1;
		$student->save();
		
		$s_ids=DB::sql("SELECT id FROM students WHERE name='$name'");
		$s_id=$s_ids[0]['id'];
		
		$runtime_id = fopen("/home/jiakun/exercise/search/runtime/".$s_id.".txt", "a") or die("Unable to open file!");
		fwrite($runtime_id, "questionnaire\n".$questionnaire_start_time.$questionnaire_end_time."\n");
		fclose($runtime_id);
		
		$this->student_playlist($s_id);
		
	}
	function student_wait($s_id){
		if(file_exists("/home/jiakun/exercise/search/group/".$s_id."txt")){
			$stu_file = fopen("/home/jiakun/exercise/search/group/".$s_id."txt", "r") or die("Unable to open file!");
			$line = fread($stu_file,filesize($s_id."txt"));
			fclose($stu_file);	
			$line_split = explode($line,'\t');
			$name = $line_split[0];
			$loudness = $line_split[1];	$energy = $line_split[2];$happiness = $line_split[3];$speed = $line_split[4];$familiar = $line_split[5];
			
			var_dump($line_split);
			
			$student=new Axon('students');
			$student->name=$name;
			$student->loudness=$loudness; $student->energy=$energy; $student->happiness=$happiness; $student->speed=$speed; $student->familiar=$familiar;
			$s_ids=DB::sql("SELECT id FROM students WHERE name='$name'");
			return($s_id=$s_ids[0]['id']);	
		}
		else{
			sleep(15);
		}
	}
	function save_student_group_assessment(){
		
		$name=$_POST['name']; $age=$_POST['age']; $session_30min=$_POST['session_30min']; $session_60min=$_POST['session_60min']; $exercisetime=$_POST['exercisetime'];
		$loudness=$_POST['loudness']; $energy=$_POST['energy']; $happiness=$_POST['happiness']; $speed=$_POST['speed']; $familiar=$_POST['familiar'];
		//create a new student instance
		$student=new Axon('students');
		$student->name=$name;
		$student->age=$age; $student->session_30min=$session_30min; $student->session_60min=$session_60min; $student->exercisetime=$exercisetime;
		$student->loudness=$loudness; $student->energy=$energy; $student->happiness=$happiness; $student->speed=$speed; $student->familiar=$familiar; 
		//?
		$student->filter=1;
		$student->save();
		
		$s_ids=DB::sql("SELECT id FROM students WHERE name='$name'");
		$s_id=$s_ids[0]['id'];	
		
		$student_file = fopen("/home/jiakun/exercise/search/group/stu.txt", "a") or die("Unable to open file!");
		fwrite($student_file, $s_id."\t".$loudness."\t".$energy."\t".$happiness."\t".$speed."\t".$familiar."\t\n");
		fclose($student_file);
		
		//wait for group parameters
		$s_new_id = $this->student_wait($s_id);
		$this->student_playlist($s_new_id);
	}
	function write_assessment(){
		// write and record the assessment result of the patients (database)
		//1. F3::DB
		$patient_name=$_POST['patient_name'];	$language=$_POST['language'];	$specify_dialects=$_POST['specify_dialects'];	$specify_others=$_POST['specify_others'];	$ward=$_POST['ward'];	$bed=$_POST['bed'];	$referral_reason=$_POST['referral_reason'];			$mobility=$_POST['mobility'];	$diagnosis=$_POST['diagnosis'];	$medical_history=$_POST['medical_history'];			$social_history=$_POST['social_history'];	$PSTI_1_1=$_POST['PSTI_1_1'];	$PSTI_1_2=$_POST['PSTI_1_2'];			$PSTI_2_1=$_POST['PSTI_2_1'];	$PSTI_2_2=$_POST['PSTI_2_2'];	$PSTI_3=$_POST['PSTI_3'];	$PSTI_4=$_POST['PSTI_4'];			$POB_1=$_POST['POB_1'];	$POB_2=$_POST['POB_2'];	$specify_POB_2=$_POST['specify_POB_2'];	$POB_3=$_POST['POB_3'];	$POB_4=$_POST['POB_4'];	$specify_POB_4=$_POST['specify_POB_4'];	$POB_5a=$_POST['POB_5a'];			$specify_POB_5a=$_POST['specify_POB_5a'];	$POB_5b=$_POST['POB_5b'];	$specify_POB_5b=$_POST['specify_POB_5b'];			$POB_5c=$_POST['POB_5c'];	$specify_POB_5c=$_POST['specify_POB_5c'];	$POB_6a=$_POST['POB_6a'];	$POB_6b=$_POST['POB_6b'];		$POB_7=$_POST['POB_7'];	$POB_8=$_POST['POB_8'];	$POB_9=$_POST['POB_9'];	$POB_10_1=$_POST['POB_10_1']; 	$POB_10_2=$_POST['POB_10_2'];	$specify_POB_10_2=$_POST['specify_POB_10_2'];	$POB_11_1=$_POST['POB_11_1'];	$POB_11_2=$_POST['POB_11_2'];			$specify_POB_11_2=$_POST['specify_POB_11_2'];	$POB_12_1=$_POST['POB_12_1'];	$POB_12_2=$_POST['POB_12_2'];	$specify_POB_12_2=$_POST['specify_POB_12_2'];	$POB_13=$_POST['POB_13'];	$POB_14=$_POST['POB_14'];	$specify_POB_14=$_POST['specify_POB_14'];	$POB_15=$_POST['POB_15'];	$MBP_1=$_POST['MBP_1'];	$MBP_2=$_POST['MBP_2'];	$MBP_2a=$_POST['MBP_2a'];	$MBP_2b=$_POST['MBP_2b'];	$specify_MBP_2b=$_POST['specify_MBP_2b'];	$MBP_2c=$_POST['MBP_2c'];	$specify_MBP_2c=$_POST['specify_MBP_2c'];	$specify_MBP_2d=$_POST['specify_MBP_2d'];	$MBP_3_1=$_POST['MBP_3_1'];	$MBP_3_2=$_POST['MBP_3_2'];	$general_recommendations=$_POST['general_recommendations'];	$therapy_type=$_POST['therapy_type'];	$NMT_protocol=$_POST['NMT_protocol'];	$time_signature=$_POST['time_signature'];	$tempo_range=$_POST['tempo_range'];	$list_songs=$_POST['list_songs'];	$assessor=$_POST['assessor'];	$date=$_POST['date'];	$exist_patient=$_POST['exist_patient'];
		//insert new patient
		if(empty($exist_patient)){
			//insert
			$patient=new Axon('patients');
			$patient->name=$patient_name;	$patient->language=$language;$patient->specify_dialects=$specify_dialects;	$patient->specify_others=$specify_others;	$patient->ward=$ward;	$patient->bed=$bed;	$patient->referral_reason=$referral_reason;	$patient->mobility=$mobility;$patient->diagnosis=$diagnosis;	$patient->medical_history=$medical_history;	$patient->social_history=$social_history;	$patient->PSTI_1_1=$PSTI_1_1;	$patient->PSTI_1_2=$PSTI_1_2;	$patient->PSTI_2_1=$PSTI_2_1;	$patient->PSTI_2_2=$PSTI_2_2;	$patient->PSTI_3=$PSTI_3;	$patient->PSTI_4=$PSTI_4;	$patient->POB_1=$POB_1;$patient->POB_2=$POB_2;$patient->specify_POB_2=$specify_POB_2;	$patient->POB_3=$POB_3;$patient->POB_4=$POB_4;$patient->specify_POB_4=$specify_POB_4;	$patient->POB_5a=$POB_5a;	$patient->specify_POB_5a=$specify_POB_5a;	$patient->POB_5b=$POB_5b;	$patient->specify_POB_5b=$specify_POB_5b;	$patient->POB_5c=$POB_5c;	$patient->specify_POB_5c=$specify_POB_5c;	$patient->POB_6a=$POB_6a;$patient->POB_6b=$POB_6b;$patient->POB_7=$POB_7;$patient->POB_8=$POB_8;$patient->POB_9=$POB_9;$patient->POB_10_1=$POB_10_1;$patient->POB_10_2=$POB_10_2;$patient->specify_POB_10_2=$specify_POB_10_2;	$patient->POB_11_1=$POB_11_1;$patient->POB_11_2=$POB_11_2;$patient->POB_12_1=$POB_12_1;$patient->POB_12_2=$POB_12_2;$patient->specify_POB_11_2=$specify_POB_11_2;	$patient->POB_13=$POB_13;$patient->POB_14=$POB_14;$patient->specify_POB_14=$specify_POB_14;	$patient->POB_15=$POB_15;$patient->MBP_1=$MBP_1;$patient->MBP_2=$MBP_2;	$patient->MBP_2a=$MBP_2a;$patient->MBP_2b=$MBP_2b;$patient->specify_MBP_2b=$specify_MBP_2b;	$patient->MBP_2c=$MBP_2c;$patient->specify_MBP_2c=$specify_MBP_2c;	$patient->specify_MBP_2d=$specify_MBP_2d;	$patient->MBP_3_1=$MBP_3_1;$patient->MBP_3_2=$MBP_3_2;$patient->general_recommendations=$general_recommendations;	$patient->therapy_type=$therapy_type;$patient->NMT_protocol=$NMT_protocol;	$patient->time_signature=$time_signature;	$patient->tempo_range=$tempo_range;	$patient->list_songs=$list_songs;	$patient->assessor=$assessor;	$patient->date=$date; $patient->specify_POB_12_2=$specify_POB_12_2;
			$me = Account::the_user();
			$patient->therapist_id=$me['id'];
			$patient->filter=1;
			$patient->save();
		}
		//update information
		else{
			$p_ids=DB::sql("SELECT `id` FROM `patients` WHERE `name`='$exist_patient'");
			$p_id=$p_ids[0]['id'];
			//var_dump($p_id);
			DB::sql("UPDATE `patients` SET `name`='$patient_name',`language`='$language',`specify_dialects`='$specify_dialects',`specify_others`='$specify_others',`ward`='$ward',`bed`='$bed',`referral_reason`='$referral_reason',`mobility`='$mobility',`diagnosis`='$diagnosis',`medical_history`='$medical_history',`social_history`='$social_history',`PSTI_1_1`='$PSTI_1_1',`PSTI_1_2`='$PSTI_1_2',`PSTI_2_1`='$PSTI_2_1',`PSTI_2_2`='$PSTI_2_2',`PSTI_3`='$PSTI_3',`PSTI_4`='$PSTI_4',`POB_1`='$POB_1',`POB_2`='$POB_2',`specify_POB_2`='$specify_POB_2',`POB_3`='$POB_3',`POB_4`='$POB_4',`specify_POB_4`='$specify_POB_4',`POB_5a`='$POB_5a',`specify_POB_5a`='$specify_POB_5a',`POB_5b`='$POB_5b',`specify_POB_5b`='$specify_POB_5b',`POB_5c`='$POB_5c',`specify_POB_5c`='$specify_POB_5c',`POB_6a`='$POB_6a',`POB_6b`='$POB_6b',`POB_7`='$POB_7',`POB_8`='$POB_8',`POB_9`='$POB_9',`POB_10_1`='$POB_10_1',`POB_10_2`='$POB_10_2',`specify_POB_10_2`='$specify_POB_10_2',`POB_11_1`='$POB_11_1',`POB_11_2`='$POB_11_2',`POB_12_1`='$POB_12_1',`POB_12_2`='$POB_12_2',`specify_POB_11_2`='$specify_POB_11_2',`POB_13`='$POB_13',`POB_14`='$POB_14',`specify_POB_14`='$specify_POB_14',`POB_15`='$POB_15',`MBP_1`='$MBP_1',`MBP_2`='$MBP_2',`MBP_2a`='$MBP_2a',`MBP_2b`='$MBP_2b',`specify_MBP_2b`='$specify_MBP_2b',`MBP_2c`='$MBP_2c',`specify_MBP_2c`='$specify_MBP_2c',`specify_MBP_2d`='$specify_MBP_2d',`MBP_3_1`='$MBP_3_1',`MBP_3_2`='$MBP_3_2',`general_recommendations`='$general_recommendations',`therapy_type`='$therapy_type',`NMT_protocol`='$NMT_protocol',`time_signature`='$time_signature',`tempo_range`='$tempo_range',`list_songs`='$list_songs',`assessor`='$assessor',`date`='$date', `specify_POB_12_2`='$specify_POB_12_2', `filter`=1 WHERE `id`='$p_id'");
			//update
			}

		//2. song show and recommend
		//var_dump($patient_name);
		$this->patient_playlist($patient_name);
		
	}
	
	function student_oldsystem_playlist($s_id){	
		$selected_tracks=DB::sql("select `track` from `student_tracks` where `student_id`='$s_id' and `suitable`='1'");
		foreach($selected_tracks as $st){
			$id=$st['track'];
			$track = DB::sql("SELECT * FROM songs WHERE id='$id'");
			$track_show[]=$track[0];
		}
		var_dump($track_show);
		F3::set('selected_num', count($track_show));
		F3::set('track_show', $track_show);
		F3::set('student_id',$s_id);
		
		//recommend_id in old DB vs 20000 songs
		$recommend_tracks=MusicRxAPI::student_20000_recommendation($s_id);
		F3::set('recommend_tracks',$recommend_tracks);
		if(count($track_show) > 0){
			F3::set('selected',1);
		}
		F3::set('page', array('student_20000_playlist', ''));
		echo Template::serve('student_20000_playlist.html');
		
	}
	
	function student_playlist($s_id){
		$selected_tracks=DB::sql("select `track`, `tempo` from `student_tracks` where `student_id`='$s_id' and `suitable`='1'");
		foreach($selected_tracks as $st){
			$tempo=$st['tempo'];
			$id=$st['track'];
			$track = DB::sql("SELECT artist_name, track_name, year, track_7digital_id, tempo, echonest_id  FROM previews  WHERE echonest_id='$id'");
			if($tempo!="0"){
				$track[0]['tempo']=$tempo;
			}
			else{
				$track[0]['tempo']=round($track[0]['tempo'],1);
			}
			$track_show[]=$track[0];
		}
		//var_dump($track_show);
		F3::set('selected_num', count($track_show));
		F3::set('track_show', $track_show);
		F3::set('student_id',$s_id);
		// filtered or recommended tracks
		$ids=DB::sql("SELECT count(id) FROM student_tracks WHERE student_id='$s_id'");
		if($ids[0]['count(id)']=="0"){
			$selected=0;
			//var_dump("first_filter");
			//filter result show
			//?
			$filter_id=MusicRxAPI::student_filter($s_id);
			//var_dump($filter_id);
			$filter_tracks=array();
			foreach($filter_id as $id){
				$ids=$id['song_id'];
				$t=DB::sql("SELECT echonest_id,artist_name,track_name,year,tempo,track_7digital_id FROM previews WHERE song_id='$ids'");
				array_push($filter_tracks,$t[0]);
			}
			//var_dump($filter_tracks);

			F3::set('filter_tracks',$filter_tracks);		
		}
		else{
			$selected=1;
			$filter=DB::sql("SELECT filter FROM students WHERE id='$s_id'");
			if($filter[0]['filter']==1){
				//var_dump("filter and recom");
				//filter function and recommend
				//?
				$filter_id=MusicRxAPI::student_filter($s_id);
				//var_dump($filter_id);
				$filter_id_array=array();
				foreach($filter_id as $id){
					array_push($filter_id_array,$id['song_id']);
				}
				//var_dump($filter_id_array);
				//
				$history_id = fopen("/home/ubuntu/storage/BEATS/search/hist/filter.filter", "w") or die("Unable to open file!");
				foreach($recommend_id as $song_id){
					fwrite($history_id, $song_id['song_id']."\n");
				}
				fclose($history_id);
				//?
				$recommend_id=MusicRxAPI::student_recommend($s_id);
				//var_dump($recommend_id);
				//
				$history_id = fopen("/home/ubuntu/storage/BEATS/search/hist/recommend.filter", "w") or die("Unable to open file!");
				foreach($recommend_id as $song_id){
					fwrite($history_id, $song_id['song_id']."\n");
				}
				fclose($history_id);
				//
				//$temp_id=array_merge($filter_id['song_id'],$recommend_id);
				$temp_id=array_merge($filter_id_array,$recommend_id);
				//var_dump($temp_id);
				//
				$history_id = fopen("/home/ubuntu/storage/BEATS/search/hist/temp.filter", "w") or die("Unable to open file!");
				foreach($recommend_id as $song_id){
					fwrite($history_id, $song_id['song_id']."\n");
				}
				fclose($history_id);
				//
				$filter_recommend_id=array_slice($temp_id,0,10);
				//var_dump($filter_recommend_id);
				$filter_recommend_tracks=array();
				foreach($filter_recommend_id as $id){
					//$ids=$id['song_id'];
					$t=DB::sql("SELECT echonest_id,artist_name,track_name,year,tempo,track_7digital_id FROM previews WHERE song_id='$id'");
					array_push($filter_recommend_tracks,$t[0]);
				}
//		$filter_recommend_tracks=DB::sql("SELECT echonest_id,artist_name,track_name,year,tempo,track_7digital_id FROM previews WHERE song_id IN (0,%s)", join(',', $filter_recommend_id)));
				F3::set('filter_recommend_tracks',$filter_recommend_tracks);
				//var_dump($filter_recommend_tracks);
			}
			else{
				//var_dump("recomm");
				//recommended function
				//?
				$recommend_id=MusicRxAPI::student_recommend($s_id);
				$recommend_tracks=array();
				foreach($recommend_id as $id){
					//$ids=$id['song_id'];
					$t=DB::sql("SELECT echonest_id,artist_name,track_name,year,tempo,track_7digital_id FROM previews WHERE song_id='$id'");
					array_push($recommend_tracks,$t[0]);
				}
				F3::set('recommend_tracks',$recommend_tracks);
			}
		}
		//$selected=0;
		//var_dump($selected);
		//?
		F3::set('selected',$selected);
		F3::set('page', array('student_playlist', ''));
		echo Template::serve('student_playlist.html');
	}
		
	function patient_playlist($patient_name){
		$p_ids=DB::sql("SELECT id FROM patients WHERE name='$patient_name'");
		$p_id=$p_ids[0]['id'];
		//var_dump($p_ids);
		// selected tracks:
		$selected_tracks=DB::sql("select `track`, `tempo` from `tracks` where `patient_id`='$p_id' and `suitable`='1'");
		foreach($selected_tracks as $st){
			$tempo=$st['tempo'];
			$id=$st['track'];
			$track = DB::sql("SELECT artist_name, track_name, year, track_7digital_id, tempo, echonest_id  FROM previews  WHERE echonest_id='$id'");
			if($tempo!="0"){
				$track[0]['tempo']=$tempo;
			}
			else{
				$track[0]['tempo']=round($track[0]['tempo'],1);
			}
			$track_show[]=$track[0];
		}
		//var_dump($track_show);
		F3::set('track_show', $track_show);
		F3::set('patient_id',$p_id);
		// filtered or recommended tracks
		$ids=DB::sql("SELECT count(id) FROM tracks WHERE patient_id='$p_id'");
		//var_dump($ids);	
		//$recommend_tracks=array(array("echonest_id"=>"TRAAAAK128F9318786","artist_name"=>"Adelitas Way","track_name"=>"Scream","year"=>"2009","tempo"=>"99.4","track_7digital_id"=>"5504670"),array("echonest_id"=>"TRAAAAV128F421A322","artist_name"=>"Western Addiction","track_name"=>"A Poor Recipe For Civic Cohesion","year"=>"2006","tempo"=>"125.4","track_7digital_id"=>"2496043"),array("echonest_id"=>"TRAAAAW128F429D538","artist_name"=>"Casual","track_name"=>"I Didn't Mean To","year"=>"1994","tempo"=>"91.9","track_7digital_id"=>"3401791"),array("echonest_id"=>"TRAAAAY128F42A73F0","artist_name"=>"Alquimia","track_name"=>"The Lark In The Clear Air","year"=>"2007","tempo"=>"41.2","track_7digital_id"=>"1245347"),array("echonest_id"=>"TRAAABD128F429CF47","artist_name"=>"The Box Tops","track_name"=>"Soul Deep","year"=>"2000","tempo"=>"20.8","track_7digital_id"=>"3400270"));
		//$filter_tracks=array(array("echonest_id"=>"TRAAACN128F9355673","artist_name"=>"Quest_ Pup_ Kevo","track_name"=>"Hit Da Scene","year"=>"2005","tempo"=>"162.1","track_7digital_id"=>"6241077"),array("echonest_id"=>"TRAAACV128F423E09E","artist_name"=>"Super Deluxe","track_name"=>"Come On (Album Version)","year"=>"2008","tempo"=>"101.1","track_7digital_id"=>"1418311"),array("echonest_id"=>"TRAAADJ128F4287B47","artist_name"=>"Big Brother & The Holding Company","track_name"=>"Heartache People","year"=>"2007","tempo"=>"100.4","track_7digital_id"=>"2978496"),array("echonest_id"=>"TRAAADT12903CCC339","artist_name"=>"Stanley Black","track_name"=>"Andalucia","year"=>"1990","tempo"=>"100.4","track_7digital_id"=>"8946015"),array("echonest_id"=>"TRAAADZ128F9348C2E","artist_name"=>"Sonora Santanera","track_name"=>"Amor De Cabaret","year"=>"2010","tempo"=>"160.3","track_7digital_id"=>"5703798"));
		//$filter_recommend_tracks=array(array("echonest_id"=>"TRAAAAK128F9318786","artist_name"=>"Adelitas Way","track_name"=>"Scream","year"=>"2009","tempo"=>"99.4","track_7digital_id"=>"5504670"),array("echonest_id"=>"TRAAAAV128F421A322","artist_name"=>"Western Addiction","track_name"=>"A Poor Recipe For Civic Cohesion","year"=>"2006","tempo"=>"125.4","track_7digital_id"=>"2496043"),array("echonest_id"=>"TRAAAAW128F429D538","artist_name"=>"Casual","track_name"=>"I Didn't Mean To","year"=>"1994","tempo"=>"91.9","track_7digital_id"=>"3401791"),array("echonest_id"=>"TRAAAAY128F42A73F0","artist_name"=>"Alquimia","track_name"=>"The Lark In The Clear Air","year"=>"2007","tempo"=>"41.2","track_7digital_id"=>"1245347"),array("echonest_id"=>"TRAAABD128F429CF47","artist_name"=>"The Box Tops","track_name"=>"Soul Deep","year"=>"2000","tempo"=>"20.8","track_7digital_id"=>"3400270"),array("echonest_id"=>"TRAAACN128F9355673","artist_name"=>"Quest_ Pup_ Kevo","track_name"=>"Hit Da Scene","year"=>"2005","tempo"=>"162.1","track_7digital_id"=>"6241077"),array("echonest_id"=>"TRAAACV128F423E09E","artist_name"=>"Super Deluxe","track_name"=>"Come On (Album Version)","year"=>"2008","tempo"=>"101.1","track_7digital_id"=>"1418311"),array("echonest_id"=>"TRAAADJ128F4287B47","artist_name"=>"Big Brother & The Holding Company","track_name"=>"Heartache People","year"=>"2007","tempo"=>"100.4","track_7digital_id"=>"2978496"),array("echonest_id"=>"TRAAADT12903CCC339","artist_name"=>"Stanley Black","track_name"=>"Andalucia","year"=>"1990","tempo"=>"100.4","track_7digital_id"=>"8946015"),array("echonest_id"=>"TRAAADZ128F9348C2E","artist_name"=>"Sonora Santanera","track_name"=>"Amor De Cabaret","year"=>"2010","tempo"=>"160.3","track_7digital_id"=>"5703798"));
		//var_dump($recommend_tracks);
		//var_dump($filter_tracks);
		if($ids[0]['count(id)']=="0"){
			$selected=0;
			var_dump("first_filter");
			//filter result show
			$filter_id=MusicRxAPI::filter($p_id);
			//var_dump($filter_id);
			$filter_tracks=array();
			foreach($filter_id as $id){
				$ids=$id['song_id'];
				$t=DB::sql("SELECT echonest_id,artist_name,track_name,year,tempo,track_7digital_id FROM previews WHERE song_id='$ids'");
				array_push($filter_tracks,$t[0]);
			}
			//var_dump($filter_tracks);

			F3::set('filter_tracks',$filter_tracks);		
		}
		else{
			$selected=1;
			$filter=DB::sql("SELECT filter FROM patients WHERE id='$p_id'");
			if($filter[0]['filter']==1){
				var_dump("filter and recom");
				//filter function and recommend
				$filter_id=MusicRxAPI::filter($p_id);
				//
				$history_id = fopen("/home/ubuntu/storage/BEATS/search/hist/filter.filter", "w") or die("Unable to open file!");
				foreach($recommend_id as $song_id){
					fwrite($history_id, $song_id['song_id']."\n");
				}
				fclose($history_id);
				//
				$recommend_id=MusicRxAPI::recommend($p_id);
				//
				$history_id = fopen("/home/ubuntu/storage/BEATS/search/hist/recommend.filter", "w") or die("Unable to open file!");
				foreach($recommend_id as $song_id){
					fwrite($history_id, $song_id['song_id']."\n");
				}
				fclose($history_id);
				//
				$temp_id=array_merge($filter_id['song_id'],$recommend_id);
				//
				$history_id = fopen("/home/ubuntu/storage/BEATS/search/hist/temp.filter", "w") or die("Unable to open file!");
				foreach($recommend_id as $song_id){
					fwrite($history_id, $song_id['song_id']."\n");
				}
				fclose($history_id);
				//
				$filter_recommend_id=array_slice($temp_id,0,10);
				$filter_recommend_tracks=array();
				foreach($filter_recommend_id as $id){
					//$ids=$id['song_id'];
					$t=DB::sql("SELECT echonest_id,artist_name,track_name,year,tempo,track_7digital_id FROM previews WHERE song_id='$id'");
					array_push($filter_recommend_tracks,$t[0]);
				}
//		$filter_recommend_tracks=DB::sql("SELECT echonest_id,artist_name,track_name,year,tempo,track_7digital_id FROM previews WHERE song_id IN (0,%s)", join(',', $filter_recommend_id)));
				F3::set('filter_recommend_tracks',$filter_recommend_tracks);
			}
			else{
				var_dump("recomm");
				//recommended function
				$recommend_id=MusicRxAPI::recommend($p_id);
				$recommend_tracks=array();
				foreach($recommend_id as $id){
					//$ids=$id['song_id'];
					$t=DB::sql("SELECT echonest_id,artist_name,track_name,year,tempo,track_7digital_id FROM previews WHERE song_id='$id'");
					array_push($recommend_tracks,$t[0]);
				}
				F3::set('recommend_tracks',$recommend_tracks);
			}
		}
		//$selected=0;
		//var_dump($selected);
		F3::set('selected',$selected);
		F3::set('page', array('patient_playlist', ''));
		echo Template::serve('patient_playlist.html');
	}
	function student_save_20000_tracks(){
		$student_id=$_POST['student_id'];
		foreach($_POST as $key => $val){
			$pos=strpos($key,"choice");
			if($pos!==false)
			{
				$tracks = new Axon('student_tracks');
				$tracks->student_id=$student_id;
				$id=substr($key,0,$pos-1);
				$suitable=$val;
				$tempo=0; // no tempo info
				//insert
				$tracks->track=$id;
                $tracks->suitable=$suitable;
				$tracks->tempo=$tempo;
				$tracks->save();
			}
		}
		$final_tracks=DB::sql("SELECT `track` FROM student_tracks WHERE student_id='$student_id' AND `suitable`='1'");
		if(count($final_tracks) >= 10){
			foreach($final_tracks as $st){
			$id=$st['track'];
			$track = DB::sql("SELECT * FROM songs  WHERE id='$id'");
			$track_show[]=$track[0];
			}
			//var_dump($track_show);
			F3::set('selected_num', count($track_show));
			F3::set('track_show', $track_show);
			F3::set('student_id', $student_id);
			F3::set('page', array('evaluation_20000', ''));
			echo Template::serve('evaluation_20000.html');
		}
		else{
			$name=DB::sql("SELECT name FROM students WHERE id='$student_id'");
			//$this->student_playlist($name[0]['name']);
			$this->student_oldsystem_playlist($student_id);
		}
	}
	
	function student_save_tracks(){
		// save the recommended tracks to database
		$student_id=$_POST['student_id'];
		DB::sql("UPDATE students SET filter=0 WHERE id='$student_id'");	
		//set filter as 0
		foreach($_POST as $key => $val){
			$pos=strpos($key,"choice");
			if($pos!==false)
			{
				$tracks = new Axon('student_tracks');
				$tracks->student_id=$student_id;
				$echonest_id=substr($key,0,$pos-1);
				//var_dump($echonest_id);
				$suitable=$val;
				$tempo=$_POST[$echonest_id."_tempo"];
				//var_dump($val);
				//var_dump($tempo);
				//insert
				$tracks->track=$echonest_id;
                $tracks->suitable=$suitable;
				$tracks->tempo=$tempo;
				//var_dump($tracks);
				$tracks->save();
			}
		}
		$final_tracks=DB::sql("SELECT `track`, `tempo` FROM student_tracks WHERE student_id='$student_id' AND `suitable`='1'");
		//var_dump($final_tracks[0]['track']);
		//var_dump("final_tracks".count($final_tracks));
		if(count($final_tracks) >= 10){
			foreach($final_tracks as $st){
				
			$tempo=$st['tempo'];
			$id=$st['track'];
			$track = DB::sql("SELECT track_name, track_7digital_id, tempo, echonest_id  FROM previews  WHERE echonest_id='$id'");
			if($tempo!="0"){
				$track[0]['tempo']=$tempo;
			}
			else{
				$track[0]['tempo']=round($track[0]['tempo'],1);
			}
			$track_show[]=$track[0];
			}
			//var_dump($track_show);
			F3::set('selected_num', count($track_show));
			F3::set('track_show', $track_show);
			F3::set('student_id', $student_id);
			F3::set('page', array('evaluation', ''));
			echo Template::serve('evaluation.html');
		}
		else{
			$name=DB::sql("SELECT name FROM students WHERE id='$student_id'");
			//$this->student_playlist($name[0]['name']);
			$this->student_playlist($student_id);
		}
	}

	function save_tracks(){
		// save the recommended tracks to database
		if(!Account::is_login()){ F3::reroute('/login'); }
		else{
		$patient_id=$_POST['patient_id'];
		DB::sql("UPDATE patients SET filter=0 WHERE id='$patient_id'");	
		$me=Account::the_user();
		$therapist_id=$me['id'];

	 	//var_dump($therapist_id);
		//set filter as 0
		foreach($_POST as $key => $val){
			$pos=strpos($key,"choice");
			if($pos!==false)
			{
				$tracks = new Axon('tracks');
				$tracks->patient_id=$patient_id;
				$tracks->therapist_id=$therapist_id;
				$echonest_id=substr($key,0,$pos-1);
				//var_dump($echonest_id);
				$suitable=$val;
				$tempo=$_POST[$echonest_id."_tempo"];
				//var_dump($val);
				//var_dump($tempo);
				//insert
				$tracks->track=$echonest_id;
                $tracks->suitable=$suitable;
				$tracks->tempo=$tempo;
				//var_dump($tracks);
				$tracks->save();
			}
		}
		F3::reroute('/');
		}
	}
	function remove_track(){
		$echonest_id=$_POST['echonest_id'];
		$patient_id=$_POST['patient_id'];
		DB::sql("UPDATE tracks SET suitable=0 WHERE patient_id='$patient_id' and track='$echonest_id'");
	}
	function remove_student_track(){
		$echonest_id=$_POST['echonest_id'];
		$student_id=$_POST['student_id'];
		DB::sql("UPDATE student_tracks SET suitable=0 WHERE student_id='$student_id' and track='$echonest_id'");
		$remove_file = fopen("/home/jiakun/exercise/search/hist/".$student_id.".remove", "a") or die("Unable to open file!");
		fwrite($remove_file, $echonest_id."\n");
		fclose($remove_file);
		
	}
	
	function remove_patient($patient_name){
		$p_ids=DB::sql("DELETE FROM patients WHERE name='$patient_name'");
		echo "delete";
		F3::reroute('/');
	}
};
?>
