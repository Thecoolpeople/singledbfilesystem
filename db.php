<?php
    ini_set('display_errors',1); error_reporting(E_ALL);
    $config = array(
        "pw" => "password",                 //master password
        "savelocation" => "./data/",        //the last '/' is very important!
    );
    $errors = array(
        "no_pw" => "ERROR: You have not pushed the right password",
        "no_dataformat" => "ERROR: Please use a valid dataformat",
    );
?>
<?php
    //get params
    $params = array(
        "password" => get_parameter("password"),
        "mode" => get_parameter("mode"),
        "path" => get_parameter("path"),
        "command" => get_parameter("command"),
        "data" => get_parameter("data"),
        "dataformat" => get_parameter("dataformat"),
    );

    //check if there is a password or mode is empty
    if($params["password"] != $config["pw"] && $params["mode"] != ""){ echo $errors["no_pw"]; exit;}

    //create new path
    if (!file_exists($config["savelocation"])){ mkdir($config["savelocation"], 0777);}              //if savelocation has no folder, create one
    $names = array("sqlite", "excel", "filesystem", "registrationsystem");
    for($i=0;$i<count($names);$i++)
        if (!file_exists($config["savelocation"]."/".$names[$i])){ mkdir($config["savelocation"]."/".$names[$i], 0777);}              //if savelocation has no folder, create one

    if($params["mode"]) $params["path"] = $config["savelocation"]."/".$params["mode"].'/'.hash("sha512", $params["path"]);      //create the new path for the file

    //convert data to array
    switch($params["dataformat"]){
        case "JSON": $params["data"] = json_decode($params["data"], true)[0]; break;
        case "base64": $params["data"] = base64_decode($params["data"]); break;
        case "string": $params["data"] = $params["data"]; break;
        //case "XML": break;    https://stackoverflow.com/questions/6578832/how-to-convert-xml-into-array-in-php            ?????
        //default: echo $errors["no_dataformat"]; exit;
    }
?>
<?php
    //run the database system
    switch($params["mode"]){
        case "": db_help($params); break;
        case "gui": gui($params); break;
        case "filesystem": echo filesystem($params); break;
        case "sqlite": echo sqlite($params); break;
        case "mysql": echo mysqldb($params); break;
        case "registrationsystem": echo registrationsystem($params); break;
        default: echo "please use valid mode";
    }
?>
<?php
    //////////////////////////////////////////////////////
    //          DB  functions                           //
    //////////////////////////////////////////////////////
    function db_help($params){
        echo '<head><title>Single DB/file system</title></head>';
        echo '<h1>DB and File System - Help</h1>';
        echo '<h2>path</h2>';
        echo '<ul><li>path=YourPath.sqlite: The file / db will be saved here</li></ul>';
        echo '<h2>mode</h2>';
        echo '<ul><li>mode=filesystem: You will use the filesystem for reading and writing data</li><li>mode=sqlite: You will store the data in a sqlite database</li><li>mode=gui: You can edit the whole data in the gui mode</li></ul>';
        echo '<h2>commands filesystem</h2>';
        echo '<ul><li>command=download: download the file</li><li>command=upload: upload the file</li><li>command=getmd5: get the md5 string of the file</li></ul>';
        echo '<h2>commands sqlite</h2>';
        echo '<ul><li>command=download: download the sqlite database</li><li>command=upload: upload the sqlite database</li><li>command=create: You can create a table with this function</li><li>command=query: run a query command on the table</li></ul>';
        echo '<h2>data</h2>';
        echo '<ul><li>data=YourData: Put in here the nessesary data for the command</li></ul>';
        echo '<h2>dataformat</h2>';
        echo '<ul><li>dataformat=TheFormatOfTheData: Available Dataformats: JSON, base64, string</li></ul>';

        echo '<h1>Tutorials</h1>';
        echo 'send all the data as get or post. The variable\'s name is json.<br>db.php?json=JSONSTRING<br>';
        echo '<script src="https://cdn.jsdelivr.net/gh/google/code-prettify@master/loader/run_prettify.js"></script>';          //pretify the code
        echo '<script>function show(id){ if(document.getElementById){ var mydiv = document.getElementById(id); mydiv.style.display = (mydiv.style.display==\'block\'?\'none\':\'block\');}}</script>';    //show the div
        function create_form($pw, $mode, $path, $command, $data, $dataformat){return '<form action="./db.php" method="get" target="_blank"><table><tr><td>password:</td><td><input type="text" name="password" value="'.$pw.'"/></td></tr><tr><td>mode:</td><td><input type="text" name="mode" value="'.$mode.'"/></td></tr><tr><td>path:</td><td><input type="text" name="path" value="'.$path.'"/></td></tr><tr><td>command:</td><td><input type="text" name="command" value="'.$command.'"/></td></tr><tr><td>data:</td><td><input type="text" name="data" size="'.(strlen($data)+12).'" value="'.$data.'"/></td></tr><tr><td>dataformat:</td><td><input type="text" name="dataformat" value="'.$dataformat.'"/></td></tr><tr><td><input type="submit"></td></tr></table></form>';}
        function create_pre($pw, $mode, $path, $command, $data, $dataformat){return '<pre class="prettyprint">[{<br>&nbsp;"password": "'.$pw.'",<br>&nbsp;"mode": "'.$mode.'",<br>&nbsp;"path": "'.$path.'",<br>&nbsp;"command": "'.$command.'",<br>&nbsp;"data": "'.$data.'",<br>&nbsp;"dataformat": "'.$dataformat.'"<br>}]</pre>';}

        echo '<button onclick="javascript:show(\'div_sqlite\'); return false">sqlite</button><button onclick="javascript:show(\'div_filesystem\'); return false">filesystem</button><button onclick="javascript:show(\'div_registrationsystem\'); return false">registrationsystem</button>';
        echo '<div style="display: none" id="div_sqlite">';
            echo '<h2>sqlite</h2>';
            echo '<h3>Create new table:</h3>';
            echo create_pre("password", "sqlite", "test", "query", "CREATE TABLE IF NOT EXISTS player(id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT NOT NULL DEFAULT '0', mail TEXT NOT NULL DEFAULT '0')", "string");
            echo '<h3>insert in table:</h3>';
            echo create_pre("password", "sqlite", "test", "query", "INSERT INTO player(name, mail) VALUES ('playername', 'email@example.com')", "string");
            echo '<h3>select from table:</h3>';
            echo create_pre("password", "sqlite", "test", "queryOutput", "select * from player", "string");
            echo '<h3>Download database</h3>';
            echo create_pre("password", "sqlite", "test", "download", "", "base64");
            echo create_pre("password", "sqlite", "test", "download", "", "");
            echo '<h3>Upload database</h3>';
            echo create_pre("password", "sqlite", "test", "upload", "<-THE DATABASE->", "base64");
        echo '</div>';

        echo '<div style="display: none" id="div_filesystem">';
            echo '<h2>filesystem</h2>';
            echo '<h3>Write content to file:</h3>';
            echo 'you will write Hello to the file';
            echo create_pre("password", "filesystem", "filename", "upload", "SGFsbG8=", "base64");
            echo '<h3>Read content from the file:</h3>';
            echo 'get the data as string';
            echo create_pre("password", "filesystem", "filename", "download", "", "");
            echo 'get the data as base64';
            echo create_pre("password", "filesystem", "filename", "download", "", "base64");
            echo '<h3>Get md5 string from the file:</h3>';
            echo create_pre("password", "filesystem", "filename", "getmd5", "", "");
        echo '</div>';

        echo '<div style="display: none" id="div_registrationsystem">';
            echo '<h2>registrationsystem</h2>';
            echo 'With the User Registration System(URS) you can handle user spezific data.';
            echo '<h3>register new user</h3>';
            echo 'With the registerEmail, the system will look, that there is no same email';
            echo create_pre("password", "registrationsystem", "playerfilename", "registerEmail", '[{\"name\":\"thecoolpeople\",\"mail\":\"mail@example.com\",\"password\":\"secret(please use hash!)\",\"data\":\"here you can put spezific userdata in any format!\"}]', "JSON");
            echo 'With the registerName, the system will look, that there is no same name';
            echo create_pre("password", "registrationsystem", "playerfilename", "registerName", '[{\"name\":\"thecoolpeople\",\"mail\":\"mail@example.com\",\"password\":\"secret(please use hash!)\",\"data\":\"here you can put spezific userdata in any format!\"}]', "JSON");
            echo '<h3>log in user</h3>';
            echo 'With the loginEmail, the system will look for the email and then controll the password';
            echo create_pre("password", "registrationsystem", "playerfilename", "loginEmail", '[{\"mail\":\"mail@example.com\",\"password\":\"secret(please use hash!)\"}]', "JSON");
            echo 'With the loginName, the system will look for the name and then controll the password';
            echo create_pre("password", "registrationsystem", "playerfilename", "loginName", '[{\"name\":\"thecoolpeople\",\"password\":\"secret(please use hash!)\"}]', "JSON");
            echo '<h3>update user</h3>';
            echo 'With the updateEmail, the system will look for the email and then controll the password and then you can change mail(if not exist), name, password';
            echo create_pre("password", "registrationsystem", "playerfilename", "updateEmail", '[{\"mail\":\"mail@example.com\",\"password\":\"secret(please use hash!)\",\"name_new\":\"username\",\"mail_new\":\"mail2@example.com\",\"password_new\":\"huhu\"}]', "JSON");
            echo 'With the updateName, the system will look for the name and then controll the password and then you can change mail, name(if not exist), password';
            echo create_pre("password", "registrationsystem", "playerfilename", "updateName", '[{\"name\":\"thecoolpeople\",\"password\":\"secret(please use hash!)\",\"name_new\":\"username\",\"mail_new\":\"mail2@example.com\",\"password_new\":\"huhu\"}]', "JSON");
            echo '<h3>upload userdata</h3>';
            echo 'With the uploadEmail, the system will look for the email and then controll the password and then you can upload the userdata.';
            echo create_pre("password", "registrationsystem", "playerfilename", "uploadEmail", '[{\"email\":\"mail@example.com\",\"password\":\"secret(please use hash!)\",\"data\":\"THE DATA\"}]', "JSON");
            echo 'With the uploadName, the system will look for the name and then controll the password and then you can upload the userdata.';
            echo create_pre("password", "registrationsystem", "playerfilename", "uploadName", '[{\"name\":\"thecoolpeople\",\"password\":\"secret(please use hash!)\",\"data\":\"THE DATA\"}]', "JSON");
            echo '<h3>download userdata - IN WORK</h3>';
            echo 'With the downloadEmail, the system will look for the email and then controll the password and then you can download the userdata.';
            echo create_pre("password", "registrationsystem", "playerfilename", "downloadEmail", '[{\"email\":\"mail@example.com\",\"password\":\"secret(please use hash!)\"}]', "JSON");
            echo 'With the downloadName, the system will look for the name and then controll the password and then you can download the userdata.';
            echo create_pre("password", "registrationsystem", "playerfilename", "downloadName", '[{\"name\":\"thecoolpeople\",\"password\":\"secret(please use hash!)\"}]', "JSON");
        echo '</div>';
    }

    function gui($params){
        echo '<h1>DB and File System - GUI</h1>';
        //echo adfly_banner();

    }

    function filesystem($params){
        switch($params["command"]){
            case 'download': if($params["dataformat"]=="base64") echo base64_encode(file_read($params["path"])); else echo file_read($params["path"]); break;            //download the file
            case 'upload': file_write($params["path"], $params["data"]); break; //upload the file
            case 'getmd5': echo md5_file($params["path"]); break;                    //calculate the md5 string of a file
        }
    }

    function sqlite($params){
        //open database
        $db = new SQLite3($params["path"], SQLITE3_OPEN_CREATE | SQLITE3_OPEN_READWRITE);

        switch($params["command"]){
            case 'download': if($params["dataformat"]=="base64") return base64_encode(file_read($params["path"])); else return file_read($params["path"]); break;            //download the database
            case 'upload': return file_write($params["path"], $params["data"]); break; //upload the database

            case 'query': sqlite_query($db, $params["data"]); break;
            case 'queryOutput': $results = sqlite_query($db, $params["data"]); $array = array(); while($row = $results->fetchArray()){ for($i=0;!empty($row[$i]);$i++){unset($row[$i]);} array_push($array, $row);} return json_encode($array); break;  //run a query in the database
        }
    }

    function registrationsystem($params){
        /*
        TODO:
            update lastLogin @login
        */
        $returns = array("reg_ok"=>"register ok", "reg_not_ok"=>"register not ok", "log_ok"=>"login ok", "log_not_ok"=>"login not ok", "upd_ok"=>"update ok", "upd_not_ok"=>"update not ok", "upl_ok"=>"upload ok", "upl_not_ok"=>"upload not ok", "dow_ok"=>"downlaod ok", "dow_not_ok"=>"download not ok");
        //check if file exists
        if (!file_exists($params["path"])){
            $sqlquery = "CREATE TABLE IF NOT EXISTS player(id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT NOT NULL DEFAULT '0', mail TEXT NOT NULL DEFAULT '0', password TEXT NOT NULL DEFAULT '0', data BLOB NOT NULL DEFAULT '0', firstlogin DATE NOT NULL DEFAULT '0', lastlogin DATE NOT NULL DEFAULT '0')";
            sqlite(array("path" => $params["path"], "command" => "query", "data" => $sqlquery));
        }

        if(array_key_exists("name", $params["data"])) $name = $params["data"]["name"];
        if(array_key_exists("mail", $params["data"])) $mail = $params["data"]["mail"];
        if(array_key_exists("password", $params["data"])) $password = $params["data"]["password"];
        if(array_key_exists("data", $params["data"])) $data = $params["data"]["data"];
        $date = date('Y-m-d H:i:s');

        if(!function_exists('check_mail')){ function check_mail($params, $mail){$sqlquery = "SELECT * FROM player WHERE mail='".$mail."'"; return sqlite(array("path" => $params["path"], "command" => "queryOutput", "data" => $sqlquery));}}       //check if email exists
        if(!function_exists('check_name')){ function check_name($params, $name){$sqlquery = "SELECT * FROM player WHERE name='".$name."'"; return sqlite(array("path" => $params["path"], "command" => "queryOutput", "data" => $sqlquery));}}       //check if name exists

        switch($params["command"]){
            case 'registerEmail': if(check_mail($params, $mail) == "[]"){/*insert new player*/ $sqlquery = "INSERT INTO player(name, mail, password, data, firstlogin, lastlogin) VALUES ('$name', '$mail', '$password', '$data', '$date', '$date')"; sqlite(array("path" => $params["path"], "command" => "query", "data" => $sqlquery)); return $returns["reg_ok"];} else return $returns["reg_not_ok"]; break;
            case 'registerName':  if(check_name($params, $name) == "[]"){/*insert new player*/ $sqlquery = "INSERT INTO player(name, mail, password, data, firstlogin, lastlogin) VALUES ('$name', '$mail', '$password', '$data', '$date', '$date')"; sqlite(array("path" => $params["path"], "command" => "query", "data" => $sqlquery)); return $returns["reg_ok"];} else return $returns["reg_not_ok"]; break;
            case 'loginEmail': if(!(($result = check_mail($params, $mail)) == "[]")){/*control password*/ if(json_decode($result, true)[0]["password"] == $password) return $returns["log_ok"]; else return $returns["log_not_ok"];} else return $returns["log_not_ok"]; break;
            case 'loginName':  if(!(($result = check_name($params, $name)) == "[]")){/*control password*/ if(json_decode($result, true)[0]["password"] == $password) return $returns["log_ok"]; else return $returns["log_not_ok"];} else return $returns["log_not_ok"]; break;
            case 'updateEmail': if(registrationsystem(array("path" => $params["path"], "command"=>"loginEmail", "data"=>array("mail"=>$mail,"password"=>$password))) == $returns["log_ok"]){/*login ok*/if(array_key_exists("name_new", $params["data"])) $name_new = $params["data"]["name_new"]; if(array_key_exists("mail_new", $params["data"])) $mail_new = $params["data"]["mail_new"]; if(array_key_exists("password_new", $params["data"])) $password_new = $params["data"]["password_new"]; if(check_mail($params, $mail_new) == "[]" || $mail_new == $mail){ $sqlquery = "UPDATE player SET "; if($name_new) $sqlquery .= "name='$name_new'"; if($mail_new){ if($name_new)$sqlquery .= ", "; $sqlquery .= "mail='$mail_new'"; if($password_new)$sqlquery .= ", ";} if($password_new) $sqlquery .= "password='$password_new'"; $sqlquery .= " WHERE mail='$mail'"; sqlite(array("path" => $params["path"], "command" => "query", "data" => $sqlquery)); return $returns["upd_ok"];} return $returns["upd_not_ok"];} return $returns["upd_not_ok"]; break;
            case 'updateName':  if(registrationsystem(array("path" => $params["path"], "command"=>"loginName", "data"=>array("name"=>$name,"password"=>$password))) == $returns["log_ok"]){/*login ok*/ if(array_key_exists("name_new", $params["data"])) $name_new = $params["data"]["name_new"]; if(array_key_exists("mail_new", $params["data"])) $mail_new = $params["data"]["mail_new"]; if(array_key_exists("password_new", $params["data"])) $password_new = $params["data"]["password_new"]; if(check_name($params, $name_new) == "[]" || $name_new == $name){ $sqlquery = "UPDATE player SET "; if($name_new) $sqlquery .= "name='$name_new'"; if($mail_new){ if($name_new)$sqlquery .= ", "; $sqlquery .= "mail='$mail_new'"; if($password_new)$sqlquery .= ", ";} if($password_new) $sqlquery .= "password='$password_new'"; $sqlquery .= " WHERE name='$name'"; sqlite(array("path" => $params["path"], "command" => "query", "data" => $sqlquery)); return $returns["upd_ok"];} return $returns["upd_not_ok"];} return $returns["upd_not_ok"]; break;
            case 'uploadEmail': if(registrationsystem(array("path" => $params["path"], "command"=>"loginEmail", "data"=>array("mail"=>$mail,"password"=>$password))) == $returns["log_ok"]){/*login ok*/ sqlite(array("path" => $params["path"], "command" => "query", "data" => "UPDATE player SET data='$data' WHERE mail='$mail'")); return $returns["upl_ok"];} return $returns["upl_not_ok"]; break;
            case 'uploadName':  if(registrationsystem(array("path" => $params["path"], "command"=>"loginName", "data"=>array("name"=>$name,"password"=>$password))) == $returns["log_ok"]){/*login ok*/ sqlite(array("path" => $params["path"], "command" => "query", "data" => "UPDATE player SET data='$data' WHERE name='$name'")); return $returns["upl_ok"];} return $returns["upl_not_ok"]; break;
            case 'downloadEmail': if(registrationsystem(array("path" => $params["path"], "command"=>"loginEmail", "data"=>array("mail"=>$mail,"password"=>$password))) == $returns["log_ok"]){/*login ok*/ sqlite(array("path" => $params["path"], "command" => "queryOutput", "data" => "SELECT * FROM player WHERE mail='$mail'"))[0]["data"]; return $returns["upl_ok"];} return $returns["upl_not_ok"]; break;
            case 'downloadName':  if(registrationsystem(array("path" => $params["path"], "command"=>"loginName", "data"=>array("name"=>$name,"password"=>$password))) == $returns["log_ok"]){/*login ok*/ sqlite(array("path" => $params["path"], "command" => "queryOutput", "data" => "SELECT * FROM player WHERE name='$name'"))[0]["data"];  return $returns["dow_ok"];} return $returns["dow_not_ok"]; break;
        }
    }

    function mysqldb($params){
        //connect to mysql database

    }

    function m($params){

    }
?>
<?php
    //////////////////////////////////////////////////////
    //          usefull functions                       //
    //////////////////////////////////////////////////////

    //check if the parameter has a POST otherwise take the GET          (JSON INPUT!)
    function get_parameter($name){ if(!empty($_POST["json"])){ if(array_key_exists($name, json_decode($_POST["json"], true)[0])) return json_decode($_POST["json"], true)[0][$name];} else if(!empty($_GET["json"])){ if(array_key_exists($name, json_decode($_GET["json"], true)[0])) return json_decode($_GET["json"], true)[0][$name];} return "";}

    //read file from filesystem to string; if not exist return ""
    function file_read($path){ if (file_exists($path)){ return file_get_contents($path);} else return "";}

    //write file to filesystem from string      //overwriting exist file
    function file_write($path, $content){ file_put_contents($path, $content);}

    //make sqlite query
    function sqlite_query($db, $querystring){ return $db->query($querystring);}

    //adfly banner
    function adfly_banner(){ echo '<script type="text/javascript">var adfly_id = 12443877;var adfly_advert = \'banner\';var adfly_domain = \'a.katzmair.eu\';var frequency_cap = 5;var frequency_delay = 5;var init_delay = 0;var popunder = true;</script><script src="https://cdn.adf.ly/js/entry.js"></script>';}
?>