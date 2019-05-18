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
    if($params["mode"]) $params["path"] = $config["savelocation"].$params["mode"].'_'.hash("sha512", $params["path"]);      //create the new path for the file

    //convert data to array
    switch($params["dataformat"]){
        case "JSON": $params["data"] = json_decode($params["data"], true); break;
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
        case "filesystem": filesystem($params); break;
        case "sqlite": sqlite($params); break;
        case "mysql": mysqldb($params); break;
    }
?>




<?php
    //////////////////////////////////////////////////////
    //          DB  functions                           //
    //////////////////////////////////////////////////////
    function db_help($params){
        echo '<h1>DB and File System - Help</h1>';
        echo '<h2>path</h2>';
        echo '<ul><li>path=YourPath.sqlite: The file / db will be saved here</li></ul>';
        echo '<h2>mode</h2>';
        echo '<ul><li>mode=filesystem: You will use the filesystem for reading and writing data</li><li>mode=sqlite: You will store the data in a sqlite database</li></ul>';
        echo '<h2>commands filesystem</h2>';
        echo '<ul><li>command=download: download the file</li><li>command=upload: upload the file</li><li>command=getmd5: get the md5 string of the file</li></ul>';
        echo '<h2>commands sqlite</h2>';
        echo '<ul><li>command=download: download the sqlite database</li><li>command=upload: upload the sqlite database</li><li>command=create: You can create a table with this function</li><li>command=query: run a query command on the table</li></ul>';
        echo '<h2>data</h2>';
        echo '<ul><li>data=YourData: Put in here the nessesary data for the command</li></ul>';
        echo '<h2>dataformat</h2>';
        echo '<ul><li>dataformat=TheFormatOfTheData: Available Dataformats: JSON, base64, string</li></ul>';

        echo '<h1>Tutorials</h1>';
        function create_form($pw, $mode, $path, $command, $data, $dataformat){echo '<form action="./db.php" method="get" target="_blank"><table><tr><td>password:</td><td><input type="text" name="password" value="'.$pw.'"/></td></tr><tr><td>mode:</td><td><input type="text" name="mode" value="'.$mode.'"/></td></tr><tr><td>path:</td><td><input type="text" name="path" value="'.$path.'"/></td></tr><tr><td>command:</td><td><input type="text" name="command" value="'.$command.'"/></td></tr><tr><td>data:</td><td><input type="text" name="data" size="'.(strlen($data)+12).'" value="'.$data.'"/></td></tr><tr><td>dataformat:</td><td><input type="text" name="dataformat" value="'.$dataformat.'"/></td></tr><tr><td><input type="submit"></td></tr></table></form>';}
        echo '<h2>sqlite</h2>';
        echo '<h3>Create new table:</h3>';
        echo create_form("password", "sqlite", "test", "query", "CREATE TABLE IF NOT EXISTS tableSpieler(id INTEGER PRIMARY KEY AUTOINCREMENT, dokoname TEXT NOT NULL DEFAULT '0', dokoinfo TEXT NOT NULL DEFAULT '0', dokosince INTEGER NOT NULL DEFAULT '0')", "string");
        echo '<h3>insert in table:</h3>';
        echo create_form("password", "sqlite", "test", "query", "INSERT INTO tableSpieler(tracktable, trackspieler, trackdate) VALUES (100, 'playername', '2019.05.18')", "string");
        echo '<h3>select from table:</h3>';
        echo create_form("password", "sqlite", "test", "query", "select * from tableSpieler", "string");

    }

    function filesystem($params){
        switch($params["command"]){
            case 'download': echo file_read($params["path"]); break;            //download the file
            case 'upload': file_write($params["path"], $params["data"]); break; //upload the file
            case 'getmd5': md5_file($params["path"]); break;                    //calculate the md5 string of a file
        }
    }

    function sqlite($params){
        //open database
        $db = new SQLite3($params["path"], SQLITE3_OPEN_CREATE | SQLITE3_OPEN_READWRITE);

        switch($params["command"]){
            case 'download': echo file_read($params["path"]); break;            //download the database
            case 'upload': file_write($params["path"], $params["data"]); break; //upload the database

            case 'query': var_dump(sqlite_query($db, $params["data"])); break;            //run a query in the database
        }
    }

    function mysqldb($params){
        //connect to mysql database

    }
?>




<?php
    //////////////////////////////////////////////////////
    //          usefull functions                       //
    //////////////////////////////////////////////////////

    //check if the parameter has a POST otherwise take the GET
    function get_parameter($name){ if(!empty($_POST[$name])) return $_POST[$name]; else if(!empty($_GET[$name])) return $_GET[$name]; else return "";}

    //read file from filesystem to string; if not exist return ""
    function file_read($path){ if (file_exists($path)){ return file_get_contents($path);} else return "";}

    //write file to filesystem from string      //overwriting exist file
    function file_write($path, $content){ file_put_contents($path, $content);}

    //make sqlite query
    function sqlite_query($db, $querystring){ return $db->query($querystring);}


?>