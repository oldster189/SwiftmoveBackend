<?php
session_start();
if(session_destroy()) // Destroying All Sessions
{
      echo"<script language='javascript'>window.location='index.php';</script>"; 
}
?>