<?php
echo "Hello world ";
$name= " Arif";
$Name= " Asif";
$age= 30;

echo $name ;
echo $Name ;
echo $age ;

$name= "Arif";
$cgpa= 3.95;
$age= 30;
$isStudent= true;

var_dump($name);
echo "<br>";
var_dump($cgpa);
echo "<br>";
var_dump($age);
echo "<br>";
var_dump($isStudent);
echo "<br>";

$first="Asif";
$last="Mahmud";
$fullName= $first . " " . $last;
echo $fullName;
echo "<br>";

$marks = 72;
if ($marks >= 80) {
    echo "You got A+";
    echo "<br>";
} elseif ($marks >= 70) {
    echo "You got A";
    echo "<br>";
} elseif ($marks >= 60) {
    echo "You got A-";
    echo "<br>";
} elseif ($marks >= 50) {
    echo "You got B";
    echo "<br>";
} elseif ($marks >= 40) {
    echo "You got C";
    echo "<br>";
} else {
    echo "You failed";
    echo "<br>";
}

$day = "Friday";
switch ($day) {
    case "Sunday":
        echo "Weekend";
        break;
    case "Friday":
        echo "Holiday";
        echo "<br>";
        break;
    default:
        echo "Working day";
}

$i= 1;
while ($i <= 5) {
    echo $i . "<br>";
    $i++;
}

$i= 1;
do {
    echo $i . "<br>";
    $i++;
} while ($i <= 5);



?>