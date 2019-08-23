<?php


require_once('SqlConnection.php');

$file = filesUploader('file');

if ($file[0] == '!'){
    http_response_code(422);
    die(mb_substr($file, 1, mb_strlen($file,'utf-8')-1, 'utf-8'));
}
else
{
    if(pathinfo($file)['extension']=="xml")
        $data = Convert($file)->user;
    elseif(pathinfo($file)['extension']=="csv"){
        $csv= file_get_contents($file);
        $array = array_map("str_getcsv", explode("\n", $csv));
        $data=[];
        $header = array_shift($array);
        foreach ($array as $v) {
            if(empty($v[0])) continue;
            $obj = new stdClass;
            foreach ($header as $key => $val) {
                $obj->$val = $v[$key];
            }
            array_push($data, $obj);
        }
        
    }else{
        http_response_code(422);
        die('Неподходящий формат файла!');
    }
    $sql = new SQLCon();
    $res = $sql->SaveData($data);
    clearDir();
    echo json_encode($res);
}


function Convert($path){
    $xmlfile = file_get_contents($path); 
  
    $new = simplexml_load_string($xmlfile); 

    $con = json_encode($new); 
    
    $res = json_decode($con);
    
    return $res;
}


function filesUploader($field){
	if (!file_exists('file/')) {
		mkdir('file/', 0777, true);
	}
  	$max_count = 10;
	$upload_path = 'file/';
	$xml = '';
    $err = '';
    print_r(is_array($_FILES[$field]['name']));
	if($_FILES[$field]['name'] && count($_FILES[$field])<$max_count /*наличие поля и количество файлов*/
		&& !empty($_FILES[$field]['name'][0]) /*форму можно отправить без файла*/) {
		
		//загружаем файлы

		$filename = $_FILES[$field]['name'];
		$size = filesize($_FILES[$field]['tmp_name'])/1024; //Переводим размер файла в Кбайты
		$size = ceil($size); //	округляем размер файла до целого числа
		$typed=explode('.', $filename); 
		$nam = $typed[sizeof($typed)-2];
  		$ext='.'.$typed[sizeof($typed)-1];
		if(!isset($filename)){// Если имя файла не существует
			$err = 'Файл '.$filename.'  не соответствует требованиям, и не был загружен.';
		}
		if(move_uploaded_file($_FILES[$field]['tmp_name'], $upload_path.$filename)){
			//echo "Файл <strong>".$filename."</strong> успешно загружен";	
		}else{
			$err = 'Файл '.$filename.'  не соответствует требованиям, и не был загружен.';
		}		
		$new_name = $nam."_".date('YmdHis').$ext;
		//Переименуем файл на всякий случай что бы не было совпадений					
		rename($upload_path.$filename, $upload_path.$new_name);
		//Запомним новое имя
		$filename = $new_name;
		//Результат
		$xml .= $upload_path.$filename;
	}

	if (count($_FILES[$field])>$max_count)
		$err='Превышен объем данных.';
	if (!empty($err)){
		return '!'.$err;
		
	}else{
		return $xml;
	}
}



function clearDir(){
	$files = glob('file/*'); // get all file names
	foreach($files as $f){ // iterate files
		if(is_file($f))
			unlink($f); // delete file
	}
}