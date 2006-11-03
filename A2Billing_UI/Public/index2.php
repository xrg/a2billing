<?php 
include ("../lib/defines.php");
include ("../lib/module.access.php");

include (dirname(__FILE__)."/../lib/company_info.php");
?>
<html><head>
<link rel="shortcut icon" href="../Images/favicon.ico" >
<link rel="icon" href="../Images/animated_favicon1.gif" type="image/gif" >
<title>..:: :<?php echo CCMAINTITLE; ?>: ::..</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<style type="text/css">
<!--
body {
 scrollbar-face-color: #F8F8F8;
 scrollbar-highlight-color: #A5A5A5;
 scrollbar-3d-light-color: #E5E5E5;
 scrollbar-shadow-color: #E5E5E5;
 scrollbar-dark-shadow-color: #036;
 SCROLLBAR-BASE-COLOR: #E5E5E5;
 SCROLLBAR-ARROW-COLOR: #888888;
}
-->
</style>
</head>


<?php 
if (SHOW_TOP_FRAME){
?>
<frameset rows="70,*" cols="*" framespacing="0" frameborder="NO" border="0">
	<frame src="PP_top.php" name="TopFrame" scrolling="NO">
<?php 
}
?>
	<frameset rows="*" cols="180,*" framespacing="0" frameborder="NO" border="0">
		<frame src="PP_menu.php" name="leftFrame" scrolling="NO" noresize>
		<frame src="PP_intro.php?sectiontitle=Intro" name="mainFrame">
	</frameset>
<?php 
if (SHOW_TOP_FRAME){
?>
</frameset>
<?php 
}
?>

</html>
