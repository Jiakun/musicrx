<?php

class MusicRxHome{
	function group_assessment(){
		F3::set('page', array('student_group_assessment', ''));
		echo Template::serve('student_group_assessment.html');
	}
	function student_register(){
		F3::set('page', array('student_register', ''));
		echo Template::serve('student_register.html');
	}
	function studentassessment(){
		F3::set('page', array('studentassessment', ''));
		$date = new DateTime();
		$questionnaire_start_time = $date->format('U = Y-m-d H:i:s') . "\n";
		F3::set('questionnaire_start_time', $questionnaire_start_time);
		echo Template::serve('student_assessment.html');
	}
	function help(){
		F3::set('page', array('help', ''));
		echo Template::serve('help.html');	
	}
	function help_stu(){
		F3::set('page', array('help_stu', ''));
		echo Template::serve('help_stu.html');	
	}
	function introduction(){
		F3::set('page', array('introduction', ''));
		echo Template::serve('introduction.html');
	}

	function questionnaire(){
		if(!Account::is_login()){ F3::reroute('/login'); }
		else{
		F3::set('page', array('questionnaire', ''));
		echo Template::serve('questionnaire.html');
		}

	}
	function finish_questionnaire($mail){
		$r=DB::sql('SELECT isn_survey FROM `therapists` WHERE `email` = :email', array(':email' => $mail));
		foreach( $r as $s)
			$t=$s['isn_survey'];
		if($t==1)
			F3::reroute('/');
		else{
			F3::reroute('/questionnaire');
		}
	}

	function systemevaluation(){
		if(!Account::is_login()){ F3::reroute('/login'); }
		else{
			$me=Account::the_user();
			$email=$me['email'];
			$r=DB::sql('SELECT isn_survey FROM `therapists` WHERE `email` = :email', array(':email' => $email));
			foreach( $r as $s)
				$t=$s['isn_survey'];
			if($t==1){
				F3::set('page', array('sysevaluation', ''));
				echo Template::serve('system_evaluation.html');
			}
			else
				F3::reroute('/questionnaire');
		}
	}

	function finish_survey(){
		$me=Account::the_user();
		$email=$me['email'];
		$years=$_POST['years'];
		$degree=$_POST['degree'];
		echo($years);
		$music_play="";
		foreach($_POST['music_play'] as $s ){
			$music_play.=$s;
		}
		$music_plays=$_POST['music_plays'];
		$patient_diff="";
		foreach($_POST['patient_diff'] as $s ){
			$patient_diff.=$s;
		}
		$patient_diffs=$_POST['patient_diffs'];
		$factors=$_POST['factors'];
		$evaluation=$_POST['evaluation'];
		$My_Experience=$_POST['My_Experience'];
		$Patient_age=$_POST['Patient_age'];
		$Patient_gender=$_POST['Patient_gender'];
		$Patient_ethnicity=$_POST['Patient_ethnicity'];
		$Musical_style=$_POST['Musical_style'];
		$Musical_tempo=$_POST['Musical_tempo'];
		$rate=$_POST['rate'];		
		$requirement=$_POST['requirement'];
		$isn_survey=TRUE;
		DB::sql("UPDATE `therapists` SET `isn_survey`='$isn_survey' , `years`='$years', `degree`='$degree', `music_play`='$music_play',`music_plays`='$music_palys', `patient_diff`='$patient_diff', `patient_diffs`='$patient_diffs',`factors`='$factors', `evaluation`='$evaluation', `My_Experience`='$My_Experience',`Patient_age`='$Patient_age', `Patient_gender`='$Patient_gender',`Patient_ethnicity`='$Patient_ethnicity',`Musical_style`='$Musical_style',`Musical_tempo`='$Musical_tempo',`rate`='$rate',`requirement`='$requirement' WHERE `email`='$email'");
		F3::reroute('/');
	
	}
	function save_evaluation(){
		$me=Account::the_user();
		$email=$me['email'];
		$evaluation = new Axon('system_evaluation');
 		$result_match="";
		foreach($_POST['result_match'] as $s ){
    			$result_match.=$s;
		}
 		$result_matches=$_POST['result_matches'];
                $song_suitable="";
	  	foreach($_POST['song_suitable'] as $s ){
			$song_suitable.=$s;
  		}
		$song_suitables=$_POST['song_suitables'];
		$parameters=$_POST['parameters'];
                $para_diff="";
		foreach($_POST['para_diff'] as $s ){	
			$para_diff.=$s;
		}
       		$improvement="";
   		foreach($_POST['improvement'] as $s ){
			$improvement.=$s;
		}
		$improvements=$_POST['improvements'];
		$comments=$_POST['comments'];
		$evaluation->email=$email;
		$evaluation->result_match=$result_match;
		$evaluation->result_matches=$result_match;
		$evaluation->song_suitable= $song_suitable;
		$evaluation->song_suitables= $song_suitables;
		$evaluation->parameters=$parameters;
		$evaluation->para_diff=$para_diff;
		$evaluation->improvement=$improvement;
		$evaluation->improvements=$improvements;
		$evaluation->comments=$comments;
		$evaluation->save();
		F3::reroute('/');
	}
	
	function save_student_evaluation()
	{
		$SONG_EVALUATION_COLNAME = 'evaluation';
		$SONG_COLNAME = 'song_id';
		$evaluation = new Axon('exercise_evaluation');
		$s_id=$_POST['student_id'];
		//var_dump($s_id);
		$evaluation->student_id=$s_id;
		$song_id = array();
		$song_id=DB::sql("SELECT `track` FROM student_tracks WHERE student_id='$s_id'");
		//var_dump(count($song_id));

		//var_dump($song_id[0]['track']);
		for($i=0; $i < 10; $i++){
			$song_evaluation_col = $SONG_EVALUATION_COLNAME.($i+1);
			$song_col = $SONG_COLNAME.($i+1);
			$evaluation->$song_evaluation_col=$_POST[$i];
			$evaluation->$song_col=$song_id[$i]['track'];
		}
		//var_dump($evaluation);
		$evaluation->playlist_appropriacy=$_POST['playlist_appropriacy'];
		$evaluation->algorithm_appropriacy=$_POST['algorithm_appropriacy'];
		$evaluation->comments=$_POST['comments'];
		$evaluation->save();
	    echo Template::serve('thank_page.html');
	}

	public function patient()
	{
		if(!Account::is_login()){ F3::reroute('/login'); }
		else{
			$me = Account::the_user();
			$therapist_id=$me['id'];
			$r=DB::sql('SELECT `name` , `date` , `id`FROM `patients` where `therapist_id`=:id',array(':id'=>$therapist_id));
//			var_dump($r);
			$i=0;
        	        foreach($r as $t){
							 $i++;
                	         $patients[$i]['name'] = $t['name'];
							 $patients[$i]['date'] = $t['date'];
							 $patients[$i]['id'] = $t['id'];
 			}
                	F3::set('patients',$patients);
	                echo Template::serve('patient.html');
		//    var_dump($patients);
	        }
         }


	function checkusers(){
		if(!Account::is_login()){ F3::reroute('/login'); }

		$me = Account::the_user();
		$mail=$me['email'];
		$r=DB::sql('SELECT isn_survey FROM `therapists` WHERE `email` = :email', array(':email' => $mail));
		foreach( $r as $s)
			$t=$s['isn_survey'];
		if($t==1){
			F3::set('page', array('home', ''));
			$stats = array(
			'm_stable_duration' => 0,
			'M_stable_duration' => 3011,
			'm_stable_percent' => 0.44,
			'M_stable_percent' => 100,
			'm_run_percent' => 66.77,
			'M_run_percent' => 100,
			'm_rate' => 23.96,
			'M_rate' => 313.60,
			'm_meter' => 3,
			'M_meter' => 4,
			'm_max_tempo_drift' => 0,
			'M_max_tempo_drift' => 14.49,
			'm_max_percent_deviation' => 0.01,
			'M_max_percent_deviation' => 5,
			'm_max_successive_change' => 0,
			'M_max_successive_change' => 5,

			'm_mismatch' => -24.63,
			'M_mismatch' => 55.28,

			'm_year' => 1800,
			'M_year' => 2013,

			'm_loudness' => -100,
                        'M_loudness' => 100,
                        'm_danceability' => 0,
                        'M_danceability' => 1,
		);
		F3::set('stats', $stats);

		$suggest = array(
			'm_stable_duration' => 90,
			'M_stable_duration' => 2400,
			'm_stable_percent' => 90,
			'M_stable_percent' => 100,
			'm_run_percent' => 66.77,
			'M_run_percent' => 100,
			'm_rate' => 60,
			'M_rate' => 150,
			'm_meter' => 3,
			'M_meter' => 4,
			'm_max_tempo_drift' => 0,
			'M_max_tempo_drift' => 5,
			'm_max_percent_deviation' => 0.01,
			'M_max_percent_deviation' => 5,
			'm_max_successive_change' => 0,
			'M_max_successive_change' => 5,
			'm_mismatch' => -24.63,
			'M_mismatch' => 55.28,
			'm_year' => 1800,
			'M_year' => 2013,
			'm_loudness' => -100,
			'M_loudness' => 100,
			'm_danceability' => 0,
  			'M_danceability' => 1,
		);
		F3::set('suggest', $suggest);
		F3::reroute('/patient');
		}
		else{
			F3::reroute('/questionnaire');		
		}
	}
};

?>
