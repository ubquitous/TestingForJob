<?php

Class SQLCon{
    private $count = 10;

    function connect(){
        return new mysqli("127.0.0.1", "root", "", "Users");
    }

    //Редактирование данных
    function SaveData($users){
        $mysqli = $this->connect();
        
        /* проверка соединения */
        if ($mysqli->connect_errno) {
            printf("Не удалось подключиться: %s\n", $mysqli->connect_error);
            return "";
        }
        $stmt = $mysqli->prepare("INSERT INTO User (email, login, name, password) 
                VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE name=?, email=?, password=?");


        $sel = $mysqli->prepare("UPDATE User SET number=0");
        $sel->execute();
        $sel->close();
        
                
        $update = $mysqli->prepare("UPDATE User SET number=1 where login=?");
        $update->bind_param('s', $login);
        
        $stmt->bind_param('sssssss', $email, $login, $name, $password, $name, $email, $password);
        // $stmt->bindParam(':e', $email);
        // $stmt->bindParam(':l', $login);
        // $stmt->bindParam(':p', $password);
        $ins = 0;
        $del = 0;
        $up = 0;
        $arr=[];
        foreach ($users as $u) {
            array_push($arr, $login);
            $login = $u->login;
            $name=!empty($u->name)?$u->name:$u->login;
            $email = $u->login."@example.com";
            $password = $u->password;
            $stmt->execute();
            $update->execute();
            if($stmt->affected_rows==2)
                $up+=1;
            else{
                $ins+=$stmt->affected_rows;
            }
        }
        
        $stmt->close();
        $update->close();
        
        $delete = $mysqli->prepare("DELETE FROM User WHERE number=0");
        $delete->execute();
        $del=$delete->affected_rows;
        $delete->close();

        $mysqli->close();

        $result = new stdClass;
        $result->delete=$del;
        $result->insert=$ins;
        $result->update=$up;
        $result->page = $this->get_page();
        $result->data = $this->get_data()->data;
        return $result;
    }


    //Получение данных
    function get_data($sort="name", $page=0){
        $mysqli = $this->connect();
        
        if ($mysqli->connect_errno) {
            printf("Не удалось подключиться: %s\n", $mysqli->connect_error);
            return "";
        }
        $order="";
        if(!(strpos($sort,"name") === false)){
            $order=" ORDER BY name ";
            $order.=$sort=="name"?"ASC ":"DESC ";
        }elseif(!(strpos($sort,"email") === false)){
            $order=" ORDER BY email ";
            $order.=$sort=="email"?"ASC ":"DESC ";
        }
        $stmt = $mysqli->prepare("SELECT name, email FROM User "
                                .$order
                                ." LIMIT ".($this->count*$page)
                                .", "
                                .$this->count
                                );
        $stmt->execute();
        $result = $stmt->get_result();
        $res = new stdClass;
        $res->data = [];
        while ($row = $result->fetch_array(MYSQLI_NUM))
        {
            $obj = new stdClass;
            $obj->name = array_shift($row);
            $obj->email = array_shift($row);
            array_push($res->data, $obj);
        }
        $stmt->close();
        $mysqli->close();
        return $res;
    }   
    
    
    //Количество страниц
    function get_page(){
        $mysqli = $this->connect();
        
        if ($mysqli->connect_errno) {
            printf("Не удалось подключиться: %s\n", $mysqli->connect_error);
            return "";
        }
        $stmt = $mysqli->prepare("SELECT CEILING(count(*)/".$this->count.") FROM User");
        $stmt->execute();
        $result = $stmt->get_result();
        $res="";
        while ($row = $result->fetch_array(MYSQLI_NUM))
        {
            $res = $row[0];
        }
        $stmt->close();
        $mysqli->close();
        return $res;
    }   
}