<?php
session_destroy();
$tempo = md5(base64_encode(time()));
header("Location: index?session=Expired=$tempo");
?>
