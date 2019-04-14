<html>
<head>
<title>
</title>
<style>
.eachline
{
	font-family: "Trebuchet MS",Helvetica,sans-serif;
    font-size: 0.9em;
    line-height: 3.0em;
    clear: both;
}
.setlink
{
    float: left;
    min-width: 40px;
    text-decoration: none;
    font-size: 2.2em;
    margin-left: 1em;
}
.setlink>a
{
    text-decoration: none;
}
.name
{
    float: left;
    color: #222;
    font-weight: bold;
    margin-left: 2em;
}
.note
{
    float: left;
    font-size: 0.84em;
    margin-left: 2em;
}
.i100k
{
    float: left;
    margin-left: 0.6em;
    /*font-size: 0.8em; */
    color: #333;
    margin-right: 0.6em;
}
.curposnline
{
    font-weight: bold;
    color: green;
}
@media only screen and (max-device-width: 800px)
{
    .eachline
    {
        padding-top: 4em;
    }
    .setlink
    {
        font-size: 9em;
        margin-left: 0.1em;
        min-width: 80px;
        color: #4444cc;
    }
    .i100k
    {
        margin-left: 0.3em;
        font-size: 2.6em;
    }
    .name
    {
        margin-left: 1em;
        font-size: 3em;
    }
    .note
    {
        margin-left: 1em;
        font-size: 2.4em;
    }
    .curposnline img
    {
        width: 0.5em;
        height: 0.5em;
    }
}
</style>
</head>
<body>

<?php
$base = "http://" . $_SERVER['SERVER_NAME'] . substr($_SERVER['REQUEST_URI'], 0, strpos($_SERVER['REQUEST_URI'], basename(__FILE__)));
?>

<script>
    function updateCurPosn(e, n) {
        const setCurPosnUrl = '<?php echo $base ?>' + 'api/waypoints/currentposition/' + e + '/' + n
        const xhr = new XMLHttpRequest()
        xhr.open("POST", setCurPosnUrl, true)
        xhr.send()
        setTimeout(() => {
            window.location.reload()
        }, 500)
    }

</script>

<?php

//TODO replace this with an API call
$datafile = "fulldata.json";
$fulldata = file_get_contents ($datafile);
$fulljson = json_decode($fulldata, true);


$curposnText = file_get_contents($base . "api/waypoints/currentposition");
$curposn = json_decode($curposnText, true);

// find posn of curposn in fulljson
for ($i = 0; $i < count($fulljson["mainWS"]); $i++)
{
    if ($fulljson["mainWS"][$i]["E"] == $curposn["E"] &&
        $fulljson["mainWS"][$i]["N"] == $curposn["N"])
    {
        break;
    }
}

$pointsToDisplay = array_slice($fulljson["mainWS"], $i-5, 160);

foreach($pointsToDisplay as $point)
{
    $curPosnClass = "";

    echo "<div class=eachline>";

    if ($point["E"] == $curposn["E"] && $point["N"] == $curposn["N"])
    {
        $curPosnClass = "curposnline";
        echo "<span class=\"setlink " . $curPosnClass . "\"><img src=\"currentHiker.png\"></span>";
    }
    else
    {
        echo "<span class=setlink><a onClick=\"updateCurPosn(" . $point["E"] . ", " . $point["N"] . ")\">&target;</a></span>";
    }

    echo "<span class=\"i100k " . $curPosnClass . "\">" . $point["E"] . "</span>" .
        "<span class=\"i100k " . $curPosnClass . "\">". $point["N"] . "</span>" .
        "<span class=\"name " . $curPosnClass . "\">" . $point["Name"] . "</span>" .
        "<span class=\"note " . $curPosnClass . "\">" . $point["Note"] . "</span>";

    echo "</div>\n";
}

?>

</body>
</html>