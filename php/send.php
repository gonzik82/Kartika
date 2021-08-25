<?php

    $ManagerID=550; //50 id Чубыкина София Константиновна Менеджер для планироания задачи
    $EventType=1004; //1004 id цели созвониться
    $SourceId=253; //253 id Источник Привлечения Лендинг
    $PositionID=50; //50 id Должности ЛПР при необходимости поставить другую
    $user_pass = "kardexpress22@yandex.ru:ma0eh12k";
    $host_api = "https://kardexpress22.mawisoft.ru";
    $UserName = $_POST['user-name'];
    $UserPhone = $_POST['phone-number'];
    $UserMail = $_POST['user-mail'];
    
    $UserName = urlencode($UserName);
    $UserMail = urlencode($UserMail);
    $today = urlencode(date("F j, Y, g:i a"));

    $UserMess=urlencode("!!Новая заявка с сайта!!");
    foreach($_POST as $key => $value)
      {
         echo "\$_POST[".$key."] = ".$value."<br>";
         $value =urlencode($value);
         $UserMess=$UserMess."%0a".$key."=".$value;
      }
    $UserMess = $UserMess."%0aDate=".$today;
      echo $UserMess;


    $UserName=str_replace (' ','_',$UserName);
    $UserMess=str_replace (' ','_',$UserMess);
    $UserPhone=str_replace (' ','-',$UserPhone);
    $UserMail=str_replace (' ','',$UserMail);

    echo $_SERVER['HTTP_REFERER'];
    $UTMsource= $_SERVER['HTTP_REFERER'];
    $findYandex   = 'utm_source=yandex';
    $findGoogle   = 'utm_source=google';
    $posYandex = strpos($UTMsource, $findYandex);
    $posGoogle = strpos($UTMsource, $findGoogle);
    if ($posYandex === false) {
        echo "Строка '$findYandex' не найдена в строке '$UTMsource'";
        if ($posGoogle === false) {
          echo "Строка '$findGoogle' не найдена в строке '$UTMsource'";
        } else {
          echo "Строка '$findGoogle' найдена в строке '$UTMsource'";
          $SourceId=351;
        }

    } else {
        echo "Строка '$findYandex' найдена в строке '$UTMsource'";
        $SourceId=257;
    }



    function InitServer($host_api, $user_pass, $cookie=''){

      $curl = curl_init();
      curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
      curl_setopt($curl, CURLOPT_USERPWD, $user_pass);
      curl_setopt($curl, CURLOPT_URL, $host_api.'/integration/init');
      curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($curl, CURLOPT_HEADER, true);
      if (strlen($cookie)>0)
        curl_setopt($curl, CURLOPT_COOKIE,$cookie);
      curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 30);
      $data = curl_exec($curl);
      $header=substr($data,0,curl_getinfo($curl,CURLINFO_HEADER_SIZE));
      $body=substr($data,curl_getinfo($curl,CURLINFO_HEADER_SIZE));
      preg_match_all("/Set-Cookie: (.*?)=(.*?);/i",$header,$res);
      $cookie='';
      foreach ($res[1] as $key => $value) {
        $cookie.= $value.'='.$res[2][$key].'; ';
      };

      if ($data === FALSE) {

        echo "cURL Error: " . curl_error($curl);
        return;
      }

      //Получаем информацию о запросе
      $info = curl_getinfo($curl);
      //Выводим какую-то инфомрацию
      echo 'Запрос выполнился за '. $info['total_time']. ' сек. к URL: '. $info['url'].'</br>';
      var_dump($body);
      echo "<br>";
      curl_close($curl);
      return $cookie;
    }

    function RequestServer($host_api, $user_pass, $param, $cookie){
      $curl = curl_init();

      curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
      curl_setopt($curl, CURLOPT_USERPWD, $user_pass);
        curl_setopt($curl, CURLOPT_COOKIE,$cookie);
      // get запрос
      curl_setopt($curl, CURLOPT_URL, $host_api . $param);
      curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
      $result = curl_exec($curl);
      // вывести результат
      curl_close($curl);
      return $result;
    }


    $cookie = InitServer($host_api, $user_pass);

    $CheckPhone="/integration/admin/clientsXML.jsp?phone=$UserPhone";
    $XMLRequest=RequestServer($host_api, $user_pass, $CheckPhone, $cookie);
    $XMLRequest = simplexml_load_string($XMLRequest);
    $len=strlen($UserPhone);
    if ($len>10) {
      $ShortPhone =substr($UserPhone, $len-10);
    } else {
      $ShortPhone = $UserPhone;
    }
    if ($XMLRequest->client != FALSE) {
      $commentAdded=false;
      foreach ($XMLRequest->client  as $client ) {
        $IDFlags=FALSE; //Обнуление флага в цаждом цикле опроса
        if ($client->person != FALSE) {
          echo "раздел персоны существует </br>";
          foreach ($client->person as $person) {
            foreach ($person->contact as $PrContact) {
              if ($PrContact->attributes()['type-code']=='phone') {
                echo "найденый телефон раздел контактные лица до форматирования $PrContact </br>";
                $PrContact=preg_replace('/[^0-9]/', '', $PrContact);
                $len=strlen($PrContact);
                if ($len>10) {
                  $PrContact =substr($PrContact, $len-10);
                }
                echo " Это телефон Раздел контактные лицабез доп символов $PrContact<br>";
                if ($PrContact ==$ShortPhone) {
                  $IDFlags=true;
                  echo " Это ИСКОМЫЙ ТЕЛЕФОН !!!<br>";
                }
              }
            }
          }
        } else {
          echo "Нет данных в разделе контактные лица<br>";
        }

        if ($client->contact!= FALSE) {
          foreach ($client->contact as $contact) {
            if ($contact->attributes()['type-code']=='phone') {
              $contact=preg_replace('/[^0-9]/', '', $contact);
              $len=strlen($contact);
              if ($len>10) {
                $contact =substr($contact, $len-10);
              }
              echo " Это телефон без доп символов раздел контакты  $contact<br>";
              if ($contact == $ShortPhone) {
                $IDFlags=true;
                echo " Это ИСКОМЫЙ ТЕЛЕФОН !!!<br>";
              }
            }
          }
        } else {
          echo "Нет контактных данных в разделе клиент";
        }
        $atr=$client->attributes();
        echo "ID  клиента ".$atr['id'];
        echo "<br>";
        if ($IDFlags == true) {
          $commentAdded=true;
          $IdClient=$atr['id'];
          echo "ID Искомого клиента $IdClient";
          $AddComent ="/integration/set/event?object.ownerName=client&object.ownerId=$IdClient&object.eventTypeId=$EventType&object.userId=$ManagerID&object.message=$UserMess&object.important&object.urgent";
          RequestServer($host_api, $user_pass, $AddComent, $cookie); // добавляем комментарий к найденному клиенту

        } else {
            echo "Совпадений телефонных номеров не найдено </br>";
            echo "<br>";
          }
      }
      if ($commentAdded == false) {
        echo "!!!!!!совпадений телефонных номеров не найдено надо созать нового клиента";
        $AddUsr= "/integration/set/client?object.name=$UserName&sourceId=$SourceId&typeCode=phone&info=$UserPhone&typeCode=email&info=$UserMail";

        $Request = RequestServer($host_api, $user_pass, $AddUsr, $cookie);

        $AddComent ="/integration/set/event?object.ownerName=client&object.ownerId=$Request&object.eventTypeId=$EventType&object.userId=$ManagerID&object.message=$UserMess&object.important&object.urgent";

        RequestServer($host_api, $user_pass, $AddComent, $cookie);

        $AddPerson = "/integration/set/person?object.clientId=$Request&object.name=$UserName&personCategoryId=$PositionID&id=&typeCode=phone&info=$UserPhone&id=&typeCode=email&info=$UserMail";

        RequestServer($host_api, $user_pass, $AddPerson, $cookie);

        $СhangeManager="/integration/set/clientPermission?id=$Request&departmentId=50&userId=$ManagerID";

        RequestServer($host_api, $user_pass, $СhangeManager, $cookie); // Закрепляем созданного клиента $Request за менеджером $ManagerID
      }
    } else {
        echo "Нет данных о телефоне Создаем нового клиента ";
        $AddUsr= "/integration/set/client?object.name=$UserName&sourceId=$SourceId&typeCode=phone&info=$UserPhone&typeCode=email&info=$UserMail";

        $Request = RequestServer($host_api, $user_pass, $AddUsr, $cookie);

        $AddComent ="/integration/set/event?object.ownerName=client&object.ownerId=$Request&object.eventTypeId=$EventType&object.userId=$ManagerID&object.message=$UserMess&object.important&object.urgent";

        RequestServer($host_api, $user_pass, $AddComent, $cookie);

        $AddPerson = "/integration/set/person?object.clientId=$Request&object.name=$UserName&personCategoryId=$PositionID&id=&typeCode=phone&info=$UserPhone&id=&typeCode=email&info=$UserMail";

        RequestServer($host_api, $user_pass, $AddPerson, $cookie);

        $СhangeManager="/integration/set/clientPermission?id=$Request&departmentId=50&userId=$ManagerID";

        RequestServer($host_api, $user_pass, $СhangeManager, $cookie); // Закрепляем созданного клиента $Request за менеджером $ManagerID
    }


    ?>
