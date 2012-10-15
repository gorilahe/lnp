<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>无标题文档</title>
</head>

<body>
<pre><?php
//print_r($_FILES);
?></pre>
<form action="" method="post" enctype="multipart/form-data" name="form1" id="form1">
  <p>
    <input type="file" name="f[s]"  />
  </p>
  <p>
    <input type="file" name="f2" />
  </p>
  <p>
    <input type="file" name="f[a]" id="f" />
  </p>
  <p>
    <input type="submit" name="button" id="button" value="提交" />
  </p>
</form>
</body>
</html>
