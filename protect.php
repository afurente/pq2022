<?php
# Tophost password
# distribuito cosi come e' senza garanzia
#
# ***************************************************************************
# Start of configuration settings

  // If you want to manage directories that are not strictly lower than this
  // directory in your webspace, change BASE_DIRECTORY: replace realpath('.')
  // with a path on your filesystem.

  define("BASE_DIRECTORY", realpath('.'));

  // If you change BASE_DIRECTORY above, you should also set $url_prefix below.
  // Generally speaking, you should put the url that correspondes with
  // BASE_DIRECTORY as the value of $url_prefix.  For instance, if you are
  // setting BASE_DIRECTORY to '/var/www', and that is the DocumentRoot for
  // your web server, you should set $url_prefix to '/' - in that case you can
  // use a relative URL. But you may have to define $url_prefix to be a fully
  // fledged URL, depending on what URL BASE_DIRECTORY corresponds to for your
  // setup. You don't have to end $url_prefix with a slash.
  $url_prefix = '';

  // The e-mail address of the sender for the password reset/assign e-mails
  $admin_email = '';

  // The template subject for the password reset/assign e-mails
  $password_subject = "Credenziali di accesso";

  // The template message for the password reset/assign e-mails
  $password_body = "

Le tue credenziali di accesso sono :

  url: %%url%%

  utente: %%login%%
  password: %%password%%

";


# End of user-modifiable code. Change nothing below this line unless you know
# what you are doing!
# ***************************************************************************

  // return images
  if (isset($_REQUEST['image'])) {
    $imagesEncoded = Array(
      "padlock"  => "iVBORw0KGgoAAAANSUhEUgAAAA4AAAALCAYAAABPhbxiAAAABmJLR0QAAAAAAAD5Q7t/AAAACXBIWXMAAAsNAAALDQHtB8AsAAAAB3RJTUUH1AQFFhQ155Nu3wAAAFRJREFUeJxjZMAE/5HYjFjkGRgYGBiYcGhiROMTBOgKcWpEt5GBgYGBjVhbYIAVagM65sGmGNnz+PyDEUjYnEoUwKrx/3/CgUm2jVTxIwcOTczYBAEMQw8nZ2fMggAAAABJRU5ErkJggg==",
      "padlock-open"      => "iVBORw0KGgoAAAANSUhEUgAAAA4AAAALCAYAAABPhbxiAAAABmJLR0QA/wAAAAAzJ3zzAAAACXBIWXMAAAsNAAALDQHtB8AsAAAAB3RJTUUH1AQFFhYN/ae0wwAAAFFJREFUeJxjYMAETGj0fySMFzAisZE1EKUZWSNWPhOaBAsDAwMrsabCACsDqn9gmAebYnT/4AKM6ALoTiUaYNX4/z/hwCPbRqr4kQOHJmZsggBvtxMm/Y4B+AAAAABJRU5ErkJggg==",
    );
    $imageDataEnc = $imagesEncoded[$_GET["image"]];
    if ($imageDataEnc) {
      $maxAge = 31536000; // one year
      $imageDataRaw = base64_decode($imageDataEnc);
      Header("Content-Type: image/png");
      Header("Content-Length: ".strlen($imageDataRaw));
      Header("Cache-Control: public, max-age=$maxAge, must-revalidate");
      echo $imageDataRaw;
    }
    exit;
  }
  
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
            "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
   <title>Tophost password</title>
   <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
   <meta http-equiv="pragma" content="no-cache">
   <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
   <meta http-equiv="pragma" content="no-cache">
<STYLE TYPE="text/css">
<!--
body  {
  background: #f9fcfe;
  color: #000;
  margin: 3;
  padding: 0;
}
      
body, td {
  font: 13px "Lucida Grande", "Lucida Sans Unicode", Tahoma, Verdana;
}
      
div.centered {
  text-align: center;
}
      
div.centered table#outside {
  border: solid 1px #000000;
  margin: 0 auto; 
  text-align: left;
  border-collapse:collapse;
}
      
td.outside {
  border-bottom: solid 1px #000000;
}
      
td {
  border-bottom: solid 0px #000000;
}
      
div.centerbold {
  text-align: center;
  font-weight: bold;
  padding: 2px 2px 2px 2px;
  margin: 5px;
  font-size: 12px;
  background-color: #ffffff;
  color: #000000;
}
      
div#notice {
  text-align: center;
  font-weight: bold;
  padding: 2px 2px 2px 2px;
  font-size: 12px;
  background-color: #00c000;
  color: #ffffff;
}
      
span.ok {
  text-align: center;
  font-weight: bold;
  padding: 2px 2px 2px 2px;
  font-size: 12px;
  background-color: #00c000;
  color: #ffffff;
}
 span.warn {
  text-align: center;
  font-weight: bold;
  padding: 2px 2px 2px 2px;
  font-size: 12px;
  background-color: #ff3300;
  color: #ffffff;
}
      
span.required {
  font-weight: bold;
  color: #ff0000;
}
div#error {
  text-align: center;
  font-weight: bold;
  padding: 2px 2px 2px 2px;
  font-size: 12px;
  background-color: #c00000;
  color: #ffffff;
}
-->
</STYLE>
</head>
<body>

<div class="centered">
<table id="outside" width="600">
<?

  define("HTACCESS", ".htaccess");
  define("HTPASSWD", ".htpasswd");
  define("VERSION", "v1.03");

  $dir = $_REQUEST['dir'];
  $action = $_REQUEST['action'];
  $login = $_REQUEST['login'];
  $newlogin = $_REQUEST['newlogin'];
  $password = $_REQUEST['password'];
  $password2 = $_REQUEST['password2'];
  $assign_random_password = $_REQUEST['assign_random_password'];
  $email = $_REQUEST['email'];
  $sender = $_REQUEST['sender'];
  $subject = $_REQUEST['subject'];
  $body = $_REQUEST['body'];
  $comment = $_REQUEST['comment'];
  $message = '';
  $pwd_printed = 0;

  # Sanitize the input
  $dir = preg_replace('/[^_a-zA-Z0-9\/\.\:]/','',$dir);
  $dir = preg_replace('/\/{1,}/','/',$dir);

	# We need this later
  $pattern = "1234567890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";

  if (!isset($_REQUEST['dir'])) $dir = '';

  if (($url_prefix != '') && (!preg_match('/\/$/',$url_prefix))) $url_prefix .= '/';
  define("URL_PREFIX", $url_prefix);

  $orig_cwd = BASE_DIRECTORY;
  $wdir = BASE_DIRECTORY . "/" . $dir;
  $wdir = preg_replace('/\/{1,}/','/',$wdir);

 function redirect($url) {
   header("HTTP/1.0 302 Redirect");
   header("Location: $url");
   header("Content-type:text/html");
?>
<!DOCTYPE HTML PUBLIC "-//IETF//DTD HTML 2.0//EN">
<HTML><HEAD>
<TITLE>302 Found</TITLE>
</HEAD><BODY>
<H1>Found</H1>
The document has moved <A HREF="<?=$url?>">here</A>.<P>
<HR>
</BODY></HTML>

<?php
   exit;
 }

  function test_secured($path) {
    // Returns 1 if 'path' is .htaccess protected.
    // Returns 2 if 'path' is .htaccess protected, but the .htaccess file was not generated by htpasstool
    $secured = 0;
    if (file_exists("$path/" . HTACCESS) && is_file("$path/" . HTACCESS)) {
      // we deliberately don't check for the presence of a HTPASSWD file here!
      $htaccess = @file_get_contents("$path/" . HTACCESS);
      if (preg_match("/^ *require valid-user/m",$htaccess)) $secured = 1; # This check is fairly basic...
      # we did not make this htaccess file!
      if (!preg_match("/^# Tophost htaccess protected/",$htaccess)) $secured = 2; 
    }
    return $secured;
  }

  function in_htpasstool_dir() {
    global $orig_cwd;
    global $wdir;

    $tmp1 = $orig_cwd;
    $tmp2 = $wdir;

    # Follow symlinks
    if (is_link($tmp1)) $tmp1 = readlink($tmp1);
    if (is_link($tmp2)) $tmp2 = readlink($tmp2);

    # Deal with relative symlinks
    if (!preg_match('/^\//',$tmp1)) $tmp1 = preg_replace('/\/[^\/]*$/',"/$tmp1",$orig_cwd);
    if (!preg_match('/^\//',$tmp2)) $tmp2 = preg_replace('/\/[^\/]*$/',"/$tmp2",$wdir);

    $tmp1 = preg_replace("/\/$/",'',$tmp1);
    $tmp2 = preg_replace("/\/$/",'',$tmp2);

    return ($tmp1 == $tmp2);
  }

  function linkit($dir,$action,$user,$pretty='') {
    if ($pretty == '') $pretty = ucfirst($action);
    $deletestr = '';
    if ($action == 'delete') {
      $deletestr = " onclick=\"return confirm('Desideri eliminare questo utente?')\"";
    }
    return "<a href=\"${$_SERVER['SCRIPT_NAME']}?action=$action&amp;dir=$dir&amp;login=$user\"$deletestr>$pretty</a>";
  }

  function warn($desc,$no_overview=0) {
    global $wdir, $dir;
    global $pwd_printed;
    if (file_exists($wdir) and (!$pwd_printed)) {
      pwd();
      $pwd_printed = 1;
    }
    print "<tr><td class=\"outside\">";
    print "<div id=\"error\">$desc</div>\n";
    print "</td></tr>";
    if (!$no_overview) overview($wdir,$dir);

  }

  function fail($desc,$no_overview=0) {
    global $wdir, $dir;
    global $pwd_printed;
    if (file_exists($wdir) and (!$pwd_printed)) {
      pwd();
      $pwd_printed = 1;
    }
    print "<tr><td class=\"outside\">";
    print "<div id=\"error\">$desc</div>\n";
    print "</td></tr>";
    if (!$no_overview) overview($wdir,$dir);

    print "</table>";
    print "</div>";

    exit;
  }
  
  function php4_scandir($dir, $sort = 0) {
     if (PHP_VERSION >= '5') return scandir($dir, $sort);
     $dirmap = array();
     if(!is_dir($dir))
     {
         trigger_error("lib::scandir($dir): failed to open dir: Invalid argument", E_USER_WARNING);
         return false;
     }
     $dir = opendir($dir);
     while (false !== ($file = readdir($dir)))
         $dirmap[] = $file;
     closedir($dir);
     ($sort == 1) ? rsort($dirmap) : sort($dirmap);
     return $dirmap;
  }

  function pwd() {
    global $wdir, $dir;
		$wd_link = URL_PREFIX . $dir;
		$wd_link2 = '';
		if (strlen($wd_link) != 0) {
			$wd_link = '<a href="' . $wd_link . '">';
			$wd_link2 = '</a>';
		}
?>
  <tr>
    <td align="center" class="outside">
      <a href="http://www.tophost.it">Tophost password <?= VERSION ?></a>
    </td>
  </tr>
  <tr>
    <td class="outside">
      <table width="100%">
        <tr>
          <td colspan="2">Cartella base: <?= BASE_DIRECTORY ?></td>
        </tr>
        <tr>
          <td>Cartella da proteggere: <?= $wd_link ?><?=$dir?><?= $wd_link2 ?> [<a href="<?=$_SERVER['SCRIPT_NAME']?>?action=changewdform&amp;dir=<?=$dir?>">cambia</a>]</td>
<?


    $test_secured = test_secured($wdir);
    if ($test_secured >= 1) {
      print "<td align=\"right\"><a href=\"${$_SERVER['SCRIPT_NAME']}?dir=$dir&amp;action=removehtaccessfileform\"><span class=\"ok\"><img src=\"$PHP_SELF?image=padlock\" alt=\"closed padlock icon\" border=0>Protetta</span></a></td></tr>";
    } else {
      print "<td align=\"right\"><a href=\"${$_SERVER['SCRIPT_NAME']}?dir=$dir&amp;action=installhtaccessfile\"><span class=\"warn\"><img src=\"$PHP_SELF?image=padlock-open\" alt=\"open padlock icon\" border=0>Non protetta</span></a></td></tr>";
    }
?>
      </table>
    </td>
  </tr>
<?

    if (!@php4_scandir($wdir)) {
      if ($_REQUEST['action'] != 'changewdform') {
        fail("La cartella da proteggere $dir non esiste",1);

      } else {
        warn("La cartella da proteggere $dir non esiste",1);
      }
    }
  }

  function overview($wdir,$dir) {
    global $message;

    if (is_file("$wdir/" . HTPASSWD) and !is_readable("$wdir/" . HTPASSWD)) {
      fail("Il file $wdir/" . HTPASSWD . " non e' leggibile. Controlla i permessi.\n",1);
    }

    if (!is_writeable($wdir)) {
      fail("La cartella $wdir non e' scrivibile. Controlla i permessi.\n",1);
    }
    if (is_file("$wdir/" . HTPASSWD) and !is_writeable("$wdir/" . HTPASSWD)) {
      fail("Il file $wdir/" . HTPASSWD . " non e' scrivibile. Controlla i permessi.\n",1);
    }

    $output = '';

    if ($message != '') {
      $output .= "<tr><td class=\"outside\">";
      if ($message != '') $output .= "<div id=\"notice\">$message</div>";
      $output .= "</td></tr>";
    }
    $output .= "<tr><td class=\"outside\">";

    $output .= "<div class=\"centerbold\">Utenti: [<a href=\"${$_SERVER['SCRIPT_NAME']}?action=addform&amp;dir=$dir\">aggiungi</a>]</div>";

    if (is_readable("$wdir/" . HTPASSWD)) {
      $fp = openfile("$wdir/" . HTPASSWD);
      $lines = readhtpasswd($fp);
      closefile($fp);
      $output .= "<div class=\"centered\">\n<table width=80% align=\"center\">";
      if (count($lines) != 0) {
        foreach ($lines as $k=>$v) {
          $output .= "<tr bgcolor=\"#FFFFFF\" onMouseOver=\"this.style.backgroundColor='#DDDDDD'\" onMouseOut=\"this.style.backgroundColor=''\">";
  
  $output .= "<td align=\"left\">$k</td><td>{$lines[$k]['comment']}</td><td>" . " " . linkit($dir,'passwordform',$k,'Password') . " " . linkit($dir,'delete',$k,'Cancella') . " " . linkit($dir,'assignpasswordform',$k,'Assegna password casuale') . " </td></tr>\n";
  
  //          $output .= "<td align=\"left\">$k</td><td>{$lines[$k]['comment']}</td><td>" . linkit($dir,'editform',$k,'Modifica') . " " . linkit($dir,'passwordform',$k,'Password') . " " . linkit($dir,'delete',$k,'Cancella') . " " . linkit($dir,'assignpasswordform',$k,'Assegna password casuale') . " </td></tr>\n";
        }
        $output .= "</table>\n</div>\n";
      } else {
        $output .= "<tr><td><div class=\"centered\">Il file " . HTPASSWD . " al momento e' vuoto.</div></td></tr>\n";
        $output .= "</table>";
        $output .= "</div>";
      }
    } else {
      $output .= "<div class=\"centered\">Il file " . HTPASSWD . " non esiste.</div>\n";
    }
    $output .= "</td></tr>";
    print $output;
  }

  function openfile($path) {
    $fp = @fopen("$path", "r+") or fail("Il file $path non puo' essere aperto",1);

    if (flock($fp, LOCK_EX)) { // do an exclusive lock
      return $fp;
    } else {
      fclose($fp);
      fail("Non riesco a bloccare il file!");
    }
  }

  function createfile($path) {
    $path = preg_replace('/\/{1,}/','/',$path);
    $fp = @fopen("$path", "w") or fail("Il file $path non puo' essere creato",1);

    if (flock($fp, LOCK_EX)) { // do an exclusive lock
      return $fp;
    } else {
      fclose($fp);
      fail("Non riesco a bloccare il file!");
    }
  }

  function removehtaccessfile($dir) {
    if (!file_exists("$dir/" . HTACCESS)) fail("Il file $dir/" . HTACCESS . " non esiste");

    @unlink("$dir/" . HTACCESS) or fail("Non riesco a eliminare $dir/" . HTACCESS);
  }

  function installhtaccessfile($dir) {

    if (file_exists("$dir/" . HTACCESS)) fail("Il file/cartella $dir/" . HTACCESS . " esiste");

    $fp = createfile("$dir/" . HTACCESS);

    $output = "# Tophost htaccess protected\nAuthType Basic\nAuthName \"Area Protetta\"\nAuthUserFile \"$dir/" . HTPASSWD . "\"\nrequire valid-user\n";
    fwrite($fp,$output);
    closefile($fp); 

  }

  function readhtpasswd($fp) {
    while (!feof($fp)) {
      $data = fgets($fp, 4096); # We assume no line is longer than 4096 characters
      if (preg_match("/^\s*$/",$data)) continue; # We don't need blank lines
      $data = preg_replace("/\n/",'',$data);
      $tmp = preg_split('/:/', $data);
      if ($tmp[0] == '') continue; # Skip blank lines or lines with empty logins
      $lines[$tmp[0]]['password'] = $tmp[1];
      $lines[$tmp[0]]['email'] = $tmp[2];
      $lines[$tmp[0]]['comment'] = $tmp[3];
    }
    return $lines;
  }

  function writehtpasswd($fp,$lines) {
    fseek($fp, 0);
    ksort($lines);
    foreach ($lines as $key => $value) {
      #print "line to write: $key:$value\n<p>";
      $output .= $key . ':' . $lines[$key]['password'] . ':'  . $lines[$key]['email'] . ':' . $lines[$key]['comment'] . "\n";
    }
#    $output = preg_replace("/\n$/",'',$output);
    fwrite($fp,$output);
    ftruncate($fp,ftell($fp));  # We want to erase any additional content in the file
  }

  function closefile($fp) {
    flock($fp, LOCK_UN); // release the lock
    fclose($fp);
  }

	function assignpasswordform() {
    global $wdir, $dir, $pwd_printed, $url_prefix;
		global $login, $admin_email, $password_subject, $password_body, $pattern;
    pwd();
    $pwd_printed = 1;

    $fp = openfile("$wdir/" . HTPASSWD);
    $lines = readhtpasswd($fp);
    closefile($fp);

    if (!array_key_exists($login ,$lines)) {
      fail("Questo utente non esiste");
    }

		$password_parsed_subject = $password_subject;

    $tmp_pass = $pattern{rand(0,61)} . $pattern{rand(0,61)} . $pattern{rand(0,61)} . $pattern{rand(0,61)} . $pattern{rand(0,61)} . $pattern{rand(0,61)} . $pattern{rand(0,61)} . $pattern{rand(0,61)};


		if ($url_prefix == '') {
	    $http = 'http://';
			if ($_SERVER['HTTPS'] != '') $http = 'https://';

			$tmpa = preg_replace('/\/[^\/]+$/','/',$_SERVER['REQUEST_URI']);
			$tmpurl = $http . $_SERVER['SERVER_NAME'] . "$tmpa$dir";
		} else {
			$tmpurl = $url_prefix . $dir;
		}

		$patterns = array("/%%admin_email%%/","/%%url%%/","/%%login%%/","/%%password%%/");
		$replacements = array($admin_email,$tmpurl,$login,$tmp_pass);

		$password_parsed_body = preg_replace($patterns,$replacements,$password_body);

?>
  <tr>
  <td class="outside">
  <form action="<?=$_SERVER['SCRIPT_NAME']?>">
  <input type="hidden" name="action" value="assignpassword">
  <input type="hidden" name="dir" value="<?=$dir?>">
  <input type="hidden" name="login" value="<?=$login?>">
  <input type="hidden" name="password" value="<?=$tmp_pass?>">
  <div class="centerbold">Assegna la password e invia una e-mail di conferma:</div>

  <div class="centered">
  <table width=80% align="center">
    <tr>
      <td align="left">Utente:</td><td align="left"><?= $login ?></td>
    </tr>
    <tr>
      <td align="left">Idirizzo e-mail:</td><td align="left"><input type="text" size="20" name="email" value="<?= $lines[$login]['email'] ?>"></td>
    </tr>
    <tr>
      <td align="left">Indirizzo e-mail del mittente:</td><td align="left"><input type="text" size="40" name="sender" value="<?= $admin_email ?>"></td>
    </tr>
    <tr>
      <td align="left">Oggetto:</td><td align="left"><input type="text" size="40" name="subject" value="<?= $password_parsed_subject ?>"></td>
    </tr>
    <tr>
      <td align="left" colspan="2">Messaggio:</td>
		</tr>
		<tr>
			<td align="left" colspan="2"><textarea cols="80" rows="15" name="body"><?= $password_parsed_body ?></textarea></td>
    </tr>
    <tr><td colspan="2"><input type="submit" value="Assegna e invia"></td></tr>
  </table>
  </div>
  </form>
  </td>
  </tr>
<?
	}

  function verify_address($email) {
    $valid = 0;
    # According to RFC 2822, these characters are valid 'atext' chars - valid for the localpart of an e-mail address
    # [a-z0-9!#\$%&'*+\-\/=?\^_`{\|}~.]
    if (preg_match("/^[a-z0-9!#\$%&'*+\-\/=?\^_\`{\|}~.]+@[a-z0-9!#\$%&'*+\-\/=?\^_\`{\|}~.]+\.[a-z0-9!#\$%&'*+\-\/=?\^_\`{\|}~]{2,}$/",$email)) $valid = 1;
    return $valid;
  }


  if ($action == 'addform') {
    pwd();
    $pwd_printed = 1;
?>
  <tr>
  <td class="outside">
  <form action="<?=$_SERVER['SCRIPT_NAME']?>">
  <input type="hidden" name="action" value="add">
  <input type="hidden" name="dir" value="<?=$dir?>">
  <div class="centerbold">Aggiungi utente:</div>
  <div class="centered">
  <table width=80% align="center">
    <tr>
      <td align="left"><span class="required">*</span>Utente:</td><td align="left"><input type="text" size="20" name="login"></td>
    </tr>
    <tr>
      <td align="left">Assegna una password casuale:</td><td align="left"><input type="checkbox" size="40" name="assign_random_password" onclick="document.getElementById('password').style.visibility = this.checked ? 'hidden' : 'visible'; document.getElementById('confirm_password').style.visibility = this.checked ? 'hidden' : 'visible'"></td>
    </tr>
    <tr id="password">
      <td align="left"><span class="required">*</span>Password:</td><td align="left"><input type="password" size="20" name="password"></div></td>
    </tr>
    <tr id="confirm_password">
      <td align="left"><span class="required">*</span>Password (conferma):</td><td align="left"><input type="password" size="20" name="password2"></td>
    </tr>
    <tr>
      <td align="left">E-mail:</td><td align="left"><input type="text" size="40" name="email"></td>
    </tr>
    <tr>
      <td align="left">Note:</td><td align="left"><input type="text" size="40" name="comment"></td>
    </tr>
    <tr><td colspan="2"><input type="submit" value="Aggiungi"></td></tr>
  </table>
  </div>
  </form>
  </td>
  </tr>
<?
  } elseif ($action == 'add') {
    if (strlen($login) < 1) fail("L'utente non puo' essere vuoto");
		if ($assign_random_password == '') {
	    if (strlen($password) < 6) fail("La password deve essere di almento 6 caratteri");
	    if ($password != $password2) fail("Le password inserite non corrispondono");
	    if ($password == '') fail("Manca un parametro");
	    if ($password2 == '') fail("Manca un parametro");
		} else {
			# Temporarily assign a random password. This is just to make sure that we
			# don't end up with accounts without passwords if the user doesn't follow
			# up on the 'assign & send' page.
    	$password = $pattern{rand(0,61)} . $pattern{rand(0,61)} . $pattern{rand(0,61)} . $pattern{rand(0,61)} . $pattern{rand(0,61)} . $pattern{rand(0,61)} . $pattern{rand(0,61)} . $pattern{rand(0,61)};
		}

    if (is_readable("$wdir/" . HTPASSWD)) {
      $fp = openfile("$wdir/" . HTPASSWD);
      $lines = readhtpasswd($fp);
    } else {
      $fp = createfile("$wdir/" . HTPASSWD);
      $lines = array();
    }

    if ((count($lines) != 0) and (array_key_exists($login,$lines))) {
      closefile($fp);
      fail("Errore: questo utente e' attivo adesso");
    }

    if ($email != '') {
      if (verify_address($email) == 0) {
        closefile($fp);
        fail("Errore: questo indirizzo e-mail non e' valido.");
      }
    }

    $lines[$login]['password'] = crypt($password,$pattern{rand(0,61)} . $pattern{rand(0,61)});
    $lines[$login]['email'] = $email;
    $lines[$login]['comment'] = $comment;
    writehtpasswd($fp,$lines);
    closefile($fp);
		if ($assign_random_password != '') {
			assignpasswordform();
		} else {
	    $message = "Utente $name creato con successo";
		}
  } elseif ($action == 'changewdform') {
    pwd();
    $pwd_printed = 1;
?>
  <tr>
  <td class="outside">
  <form action="<?=$_SERVER['SCRIPT_NAME']?>">
  <div class="centerbold">Cambia la cartella da proteggere:</div>

  <div class="centered">
  <table width=80% align="center">
    <tr>
      <td align="left">Nuova cartella da proteggere:</td><td align="left"><input type="text" size="60" name="dir"></td>
    </tr>
    <tr><td colspan="2"><input type="submit" value="Aggiorna"></td></tr>
  </table>
  </div>
  </form>
  </td>
  </tr>
<?
  } elseif ($action == 'assignpasswordform') {
		assignpasswordform();
  } elseif ($action == 'assignpassword') {
    if ($login == '') fail("Manca un parametro");
    if ($email == '') fail("L'e-mail di destinazione non puo' essere vuota");
    if ($sender == '') fail("L'e-mail del mittente non puo' essere vuota");
    if ($subject == '') fail("Il soggetto non puo' essere vuoto");
    if ($body == '') fail("Il messaggio deve essere presente");
    if (verify_address($email) == 0) fail("Indirizzo e-mail del destinatario errato");
    if (verify_address($sender) == 0) fail("Indirizzo e-mail del mittente errato");

    $fp = openfile("$wdir/" . HTPASSWD);
    $lines = readhtpasswd($fp);

    if (!array_key_exists($login ,$lines)) {
      closefile($fp);
      fail("Questo utente non esiste");
    }

    $lines[$login]['password'] = crypt($password,$pattern{rand(0,61)} . $pattern{rand(0,61)});
		# If the e-mail address on file is empty, replace it with the one that was given here. Otherwise, ignore the one that was given here.
		if ($lines[$login]['email'] == '') {
    	$lines[$login]['email'] = $email;
		}
    $lines[$login]['password'] = crypt($password,$pattern{rand(0,61)} . $pattern{rand(0,61)});
    writehtpasswd($fp,$lines);
    closefile($fp);

		$headers = "From: $sender\r\nTo: $email\r\nReply-To: $sender\r\n";
		$extra = "-f $sender";

		mail($email,$subject,$body,$headers,$extra);
		mail($sender,"(Copia carbone): $subject",$body,$headers,$extra);

    $message = "Le credenziali per l'utente $login sono state assegnate ed e' stata inviata una e-mail di conferma. Riceverai l'e-mail in copia.";
  } elseif ($action == 'passwordform') {
    pwd();
    $pwd_printed = 1;
?>
  <tr>
  <td class="outside">
  <form action="<?=$_SERVER['SCRIPT_NAME']?>">
  <input type="hidden" name="action" value="password">
  <input type="hidden" name="dir" value="<?=$dir?>">
  <input type="hidden" name="login" value="<?=$login?>">
  <div class="centerbold">Cambia password:</div>

  <div class="centered">
  <table width=60% align="center">
    <tr>
      <td align="left">Utente:</td><td align="left"><?= $login ?></td>
    </tr>
    <tr>
      <td align="left">Nuova password:</td><td align="left"><input type="password" size="20" name="password"></td>
    </tr>
    <tr>
      <td align="left">Nuova password (conferma):</td><td align="left"><input type="password" size="20" name="password2"></td>
    </tr>
    <tr><td colspan="2"><input type="submit" value="Aggiorna"></td></tr>
  </table>
  </div>
  </form>
  </td>
  </tr>
<?
  } elseif ($action == 'password') {
    if ($login == '') fail("Parametri mancanti");
    if (strlen($password) < 6) fail("La password deve essere di almeno 6 caratteri");
    if ($password == '') fail("Parametri mancanti");
    if ($password2 == '') fail("Parametri mancanti");
    if ($password != $password2) fail("Le password inserite non corrispondono");

    $fp = openfile("$wdir/" . HTPASSWD);
    $lines = readhtpasswd($fp);

    if (!array_key_exists($login ,$lines)) {
      closefile($fp);
      fail("Utente non esistente");
    }

    $lines[$login]['password'] = crypt($password,$pattern{rand(0,61)} . $pattern{rand(0,61)});
    writehtpasswd($fp,$lines);
    closefile($fp);
    $message = "La password per l'utente $login e' stata aggiornata";
  } elseif ($action == 'editform') {
    pwd();
    $pwd_printed = 1;
    $fp = openfile("$wdir/" . HTPASSWD);
    $lines = readhtpasswd($fp);
    closefile($fp);
    if (!array_key_exists($login,$lines)) {
      fail("Utente non esistente");
    }
?>
  <tr>
  <td class="outside">
  <form action="<?=$_SERVER['SCRIPT_NAME']?>">
  <input type="hidden" name="action" value="Aggiorna">
  <input type="hidden" name="dir" value="<?=$dir?>">
  <input type="hidden" name="login" value="<?=$login?>">
  <div class="centerbold">Modifica utente:</div>
  <div class="centered">
  <table width=60% align="center">
    <tr>
      <td align="left">Utente:</td><td align="left"><input type="text" size="20" name="newlogin" value="<?=$login?>"></td>
    </tr>
    <tr>
      <td align="left">E-mail:</td><td align="left"><input type="text" size="40" name="email" value="<?=$lines[$login]['email']?>"></td>
    </tr>
    <tr>
      <td align="left">Note:</td><td align="left"><input type="text" size="40" name="comment" value="<?=$lines[$login]['comment']?>"></td>
    </tr>
    <tr><td colspan="2"><input type="submit" value="Aggiorna"></td></tr>
  </table>
  </div>
  </form>
  </td>
  </tr>
<?
  } elseif ($action == 'update') {
    if ($login == '') fail("Parametri mancanti");
    if ($newlogin == '') fail("Parametri mancanti");
    $fp = openfile("$wdir/" . HTPASSWD);
    $lines = readhtpasswd($fp);

    if ($email != '') {
      if (verify_address($email) == 0) {
        closefile($fp);
        fail("Aggiornamento fallito: Questo indirizzo e-mail non e' valido.");
      }
    }

    $lines[$login]['email'] = $email;
    $lines[$login]['comment'] = $comment;
    if ($login != $newlogin) {
      $lines[$newlogin] = $lines[$login];
      unset($lines[$login]);
    }
    writehtpasswd($fp,$lines);
    closefile($fp);
    $message = "L'utente $newlogin e' stato aggiornato.";
  } elseif ($action == 'delete') {
    if ($login == '') fail("Parametro mancante");

    $fp = openfile("$wdir/" . HTPASSWD);
    $lines = readhtpasswd($fp);

    if (!array_key_exists($login,$lines)) {
      closefile($fp);
      fail("Utente non esistente");
    }

    unset($lines[$login]);
    if (in_htpasstool_dir() and(count($lines) == 0)) {
      closefile($fp);
      fail("Non puoi rimuovere l'ultimo utente dell'elenco dal file .htaccess presente nella cartella di questo script");
    }
    writehtpasswd($fp,$lines);
    closefile($fp);
    $message = "Utente $login cancellato";
  } elseif ($action == 'removehtaccessfileform') {
    pwd();
    $pwd_printed = 1;
?>
  <tr>
  <td class="outside">
  <form action="<?=$_SERVER['SCRIPT_NAME']?>">
  <input type="hidden" name="action" value="removehtaccessfile">
  <input type="hidden" name="dir" value="<?=$dir?>">
  <input type="hidden" name="login" value="<?=$login?>">
  <div class="centerbold">Cancella il file .htaccess:</div>
  <div class="centered">
  <table width=60% align="center">
    <tr>
      <td align="left" colspan="2">
        Stai per cancellare il file .htaccess dalla cartella <?=$dir?>. Questa operazione rendera' la cartella accessibile al pubblico senza alcuna richiesta di password. Sicuro di procedere?
<?
  if (test_secured($dir) == 2) {
?>
    <p><span class="warn">ATTENZIONE!</span> Questo file .htaccess non e' stato creato da questo script. Sei sicuro?
<?
  }
  if (in_htpasstool_dir()) {
?>
    <p><span class="warn">ATTENZIONE!</span> Questo file .htaccess protegge questo. Se lo rimuovi, qualsiasi persona della rete sara' in grado di impostare e rimuovere delle cartelle protette nel tuo spazio web. Sei sicuro di voler procedere?
<?
  }
?>
      </td>
    </tr>
    <tr><td><input type="submit" name="submit" value="Annulla"></td><td><input type="submit" name="submit" value="OK"></td></tr>
  </table>
  </div>
  </form>
  </td>
  </tr>
<?
  } elseif ($action == 'removehtaccessfile') {
    if ($_REQUEST['submit'] == 'OK') {
      removehtaccessfile("$wdir");
      $message = "La cartella $dir non e' piu' protetta\n";
    }
  } elseif ($action == 'installhtaccessfile') {
    # Now; IF we are working on the htpasstool directory, refuse to activate
    # protection unless there is one user defined (the minimum HTPASSWD file
    # length is 15 chars: one for the username, one for the colon, and 13 for
    # the crypt'ed password).
    if (!in_htpasstool_dir() or (is_readable("$wdir/" . HTPASSWD) and (filesize("$wdir/" . HTPASSWD) >= 15))) {
      installhtaccessfile("$wdir");
      $message = "La cartella $dir e' protetta\n";
    } else {
      fail("Quando si protegge la cartella dove e' inserito questo script devi definire almeno un utente");
    }
  } 

  if (!$pwd_printed) {
    pwd();
    $pwd_printed = 1;
  }

  overview($wdir,$dir);

?>
</table>
</div>

</body>
</html>
