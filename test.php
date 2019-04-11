<?php

$result = mail("blackysmurf2@gmail.com","My subject","Hello");

if($result === FALSE)
{
	echo "Failed";
}