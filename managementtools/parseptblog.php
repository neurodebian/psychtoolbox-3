<?php
// parseptblog.php -- PHP parser for final Psychtoolbox registration log.
//
// This PHP script parses the final Psychtoolbox registration log,
// counts number of installations and other interesting numbers and
// for historical reasons outputs its results as HTML formatted text.
//
// 1. Change the $filename string to the full path and filename of the
//    registration log file.
//
// 2. Run it like this: php parseptblog.php > statistics.html
//
// 3. Look at the statistics.html file with a web browser.
//
// History:
// 10/20/2006 Written. (MK)
// 11/03/2006 * Made more robust against corrupted lines in logfile.
//            * More detailled stats: Show distribution of Macintoshes.
//            * Improved HTML output formatting.
// 20/05/2018 * Rewritten to parse the fully anonymized final log.

// Debug flag: If set to 1, outputs diagnostic output as well.
$debugmode = 0;

date_default_timezone_set('UTC');

// Default filename for registration log file:
$filename = "./ptbregistrationlog";
if (file_exists($filename)===FALSE) {
   print "<br />The file $filename does not exist!<br />";
   return;
}

// Read logfile into $lines array:
$lines = file ($filename);

// Reset our counters:
$transactioncount = 0;
$discardedcount = 0;
$downloaddiscardedcount = 0;
$totalcount = 0;
$updatecount = 0;
$betacount = 0;
$stablecount = 0;
$trunkcount = 0;
$unknowncount = 0;
$oldptb307count = 0;
$ptb308pretigercount = 0;
$ptb308count = 0;
$ptb309count = 0;
$ptb3010count = 0;
$ptb3011oldgscount = 0;
$ptb3011count = 0;
$ptb3012count = 0;
$ptb3013count = 0;
$osxcount = 0;
$wincount = 0;
$linuxcount = 0;

$ppccount = 0;
$intelmaccount = 0;
$somemaccount = 0;

$panthercount = 0;
$tigercount = 0;
$leopardcount = 0;
$snowleopardcount = 0;
$lioncount = 0;
$mountainlioncount = 0;
$maverickscount = 0;
$yosemitecount = 0;
$elcapitancount = 0;
$sierracount = 0;
$highsierracount = 0;

$winunknowncount = 0;
$win2kcount = 0;
$winxpcount = 0;
$winvistacount = 0;
$win7count = 0;
$win8count = 0;
$win81count = 0;
$win10count = 0;

$winmatr200xcount = 0;
$winmatr2006count = 0;
$winmatrothercount = 0;

$matv5count = 0;
$matv6count = 0;
$matv7count = 0;
$matv70count = 0;
$matv71count = 0;
$matv72count = 0;
$matv73count = 0;
$matv74count = 0;
$matv75count = 0;
$matv76count = 0;
$matv77count = 0;
$matv78count = 0;
$matv79count = 0;
$matv710count = 0;
$matv711count = 0;
$matv712count = 0;
$matv713count = 0;
$matv714count = 0;
$matv80count = 0;
$matv81count = 0;
$matv82count = 0;
$matv83count = 0;
$matv84count = 0;
$matv85count = 0;
$matv86count = 0;
$matv90count = 0;
$matv91count = 0;
$matv92count = 0;
$matv93count = 0;
$matv94count = 0;

$octavelinuxcount = 0;
$octaveosxcount   = 0;
$octavewincount   = 0;
$octave4wincount  = 0;

$linux64count = 0;
$osx64count = 0;
$win64count = 0;

$linuxarmcount = 0;

$intransaction = 0;
$linescount = 0;
$curline = 0;
$corruptcount = 0;

// For each input line $fl in array do:
foreach($lines as $fl){
  // print "$fl \n\n";

  // A new line has begun:
  $linescount++;

  // Check if input line contains a <ID> --> Start of new transaction.
  $ls = strpos($fl, '<ID>');
  if ($ls !== FALSE) {
    // <ID> detected: Is this valid?
    if ($intransaction === 1) {
      // Invalid line! Tried to start a new transaction while old one still active
      // this must be a corrupted record in the logfile:
      if ($debugmode) printf("LOGPARSER-WARNING: Corrupt, unfinished transaction started at line $curline --> Skipped!<br />");

      // Increment counter of corrupt transactions:
      $corruptcount++;
    }

    // Reset processing to a sane state, so we can start a new transaction with the
    // current input line $fl:

    // Mark start of a new transaction:
    $intransaction=1;

    // Remember line number:
    $curline = $linescount;

    // Reset to empty transaction line:
    $ofl = '';
  }

  // Add current line to concatenated line:
  $ofl .= $fl;

  // End of line reached?
  $le = strpos($ofl, '</DATE>');
  if ($le === FALSE) {
    // Transaction line not complete. Just skip.
    // to next iteration.
  }
  else {
    // One transaction line finished. Do the stats.
    $ls = strpos($ofl, '<DATE>');
    $le = strpos($ofl, '</DATE>');
    $curdate = substr($ofl, $ls, $le - $ls);

    if (1 || strpos($curdate, '2011') || strpos($curdate, '2012') || strpos($curdate, '2013') || strpos($curdate, '2014') || strpos($curdate, '2015') || strpos($curdate, '2016') || strpos($curdate, '2017') || strpos($curdate, '2018')) {
    //if (strpos($curdate, '2015') || strpos($curdate, '2016') || strpos($curdate, '2017') || strpos($curdate, '2018')) {
    //if (strpos($curdate, '2016') || strpos($curdate, '2017') || strpos($curdate, '2018')) {
    //if (strpos($curdate, '2017') || strpos($curdate, '2018')) {
    //if (strpos($curdate, '2018')) {

        // This counts as one valid transaction:
        $transactioncount++;

        // Extract ID as globally unique identifier of this machine:
        $ls = strpos($ofl, '<ID>');
        $le = strpos($ofl, '</ID>');
        $uid = substr($ofl, $ls, $le - $ls + 1);

        // Create an entry in the array of ids, using uid as key:
        $uniqueptbs[$uid]=$ofl;

        // Invalid id and therefore discounted?
        if (strpos($uid, '<ID>0<') === 0) {
            $discardedcount++;
            if (strpos($ofl, '<ISUPDATE>0')) {
                $downloaddiscardedcount++;
            }
        }

    }

    // Reset to empty line:
    $ofl = '';

    // Transaction finished:
    $intransaction=0;
  }
}

// Done with parsing the log file: Do the stats on all machines.
foreach($uniqueptbs as $ofl) {
  $ls = strpos($ofl, '<DATE>');
  $le = strpos($ofl, '</DATE>');
  $curdate = substr($ofl, $ls, $le - $ls);

  // Increase total unique count:
  $totalcount++;

  // Reset assigned flag:
  $assigned = 0;

  $isoctave = 0;
  $ismatlab = 0;
  $islinux  = 0;
  $iswin    = 0;
  $isosx    = 0;

  // Restrict to date range of certain years:
  if (1 || strpos($curdate, '2010') || strpos($curdate, '2011') || strpos($curdate, '2012') || strpos($curdate, '2013') || strpos($curdate, '2014') || strpos($curdate, '2015') || strpos($curdate, '2016') || strpos($curdate, '2017') || strpos($curdate, '2018')) {
//  if (strpos($curdate, '2011') || strpos($curdate, '2012') || strpos($curdate, '2013') || strpos($curdate, '2014') || strpos($curdate, '2015') || strpos($curdate, '2016') || strpos($curdate, '2017') || strpos($curdate, '2018')) {
//  if (strpos($curdate, '2015') || strpos($curdate, '2016') || strpos($curdate, '2017') || strpos($curdate, '2018')) {
//  if (strpos($curdate, '2016') || strpos($curdate, '2017') || strpos($curdate, '2018')) {
  }
  else {
    $ofl = '';
    $totalcount--;
  }

  // Now count by category:

  // Flavor:
  if (strpos($ofl, '<FLAVOR>beta</FLAVOR>')) { $assigned++ ; $betacount++; }
  if (strpos($ofl, '<FLAVOR>current</FLAVOR>')) { $assigned++ ; $betacount++; }
  if (strpos($ofl, '<FLAVOR>stable</FLAVOR>')) { $assigned++ ; $stablecount++; }
  if (strpos($ofl, '<FLAVOR>unsupported</FLAVOR>')) { $assigned++ ; $stablecount++; }
  if (strpos($ofl, '<FLAVOR>trunk</FLAVOR>')) { $assigned++ ; $trunkcount++; }
  if (strpos($ofl, '<FLAVOR>Psychtoolbox-3.0.7</FLAVOR>')) { $assigned++ ; $oldptb307count++; }
  if (strpos($ofl, '<FLAVOR>unknown</FLAVOR>')) { $assigned++ ; $unknowncount++; }
  if (strpos($ofl, '<FLAVOR>Psychtoolbox-3.0.8-PreTiger</FLAVOR>')) { $assigned++ ; $ptb308pretigercount++; }
  if (strpos($ofl, '<FLAVOR>Psychtoolbox-3.0.8</FLAVOR>')) { $assigned++ ; $ptb308count++; }
  if (strpos($ofl, '<FLAVOR>Psychtoolbox-3.0.9</FLAVOR>')) { $assigned++ ; $ptb309count++; }
  if (strpos($ofl, '<FLAVOR>Psychtoolbox-3.0.10</FLAVOR>')) { $assigned++ ; $ptb3010count++; }
  if (strpos($ofl, '<FLAVOR>Psychtoolbox-3.0.11-PreWinGStreamerSDK</FLAVOR>')) { $assigned++ ; $ptb3011oldgscount++; }
  if (strpos($ofl, '<FLAVOR>Psychtoolbox-3.0.11</FLAVOR>')) { $assigned++ ; $ptb3011count++; }
  if (strpos($ofl, '<FLAVOR>Psychtoolbox-3.0.12</FLAVOR>')) { $assigned++ ; $ptb3012count++; }
  if (strpos($ofl, '<FLAVOR>Psychtoolbox-3.0.13</FLAVOR>')) { $assigned++ ; $ptb3013count++; }

  if (strpos($ofl, '<ENVIRONMENT>Matlab')) {
    $ismatlab = 1;

    // Matlab major version:
    if (strpos($ofl, '<ENVVERSION>5.')) { $matv5count++; }
    if (strpos($ofl, '<ENVVERSION>6.')) { $matv6count++; }
    if (strpos($ofl, '<ENVVERSION>7.')) { $matv7count++; }
    if (strpos($ofl, '<ENVVERSION>7.0')) { $matv70count++; }
    if (strpos($ofl, '<ENVVERSION>7.1.')) { $matv71count++; }
    if (strpos($ofl, '<ENVVERSION>7.2')) { $matv72count++; }
    if (strpos($ofl, '<ENVVERSION>7.3')) { $matv73count++; }
    if (strpos($ofl, '<ENVVERSION>7.4')) { $matv74count++; }
    if (strpos($ofl, '<ENVVERSION>7.5')) { $matv75count++; }
    if (strpos($ofl, '<ENVVERSION>7.6')) { $matv76count++; }
    if (strpos($ofl, '<ENVVERSION>7.7')) { $matv77count++; }
    if (strpos($ofl, '<ENVVERSION>7.8')) { $matv78count++; }
    if (strpos($ofl, '<ENVVERSION>7.9')) { $matv79count++; }
    if (strpos($ofl, '<ENVVERSION>7.10')) { $matv710count++; }
    if (strpos($ofl, '<ENVVERSION>7.11')) { $matv711count++; }
    if (strpos($ofl, '<ENVVERSION>7.12')) { $matv712count++; }
    if (strpos($ofl, '<ENVVERSION>7.13')) { $matv713count++; }
    if (strpos($ofl, '<ENVVERSION>7.14')) { $matv714count++; }
    if (strpos($ofl, '<ENVVERSION>8.0')) { $matv80count++; }
    if (strpos($ofl, '<ENVVERSION>8.1')) { $matv81count++; }
    if (strpos($ofl, '<ENVVERSION>8.2')) { $matv82count++; }
    if (strpos($ofl, '<ENVVERSION>8.3')) { $matv83count++; }
    if (strpos($ofl, '<ENVVERSION>8.4')) { $matv84count++; }
    if (strpos($ofl, '<ENVVERSION>8.5')) { $matv85count++; }
    if (strpos($ofl, '<ENVVERSION>8.6')) { $matv86count++; }
    if (strpos($ofl, '<ENVVERSION>9.0')) { $matv90count++; }
    if (strpos($ofl, '<ENVVERSION>9.1')) { $matv91count++; }
    if (strpos($ofl, '<ENVVERSION>9.2')) { $matv92count++; }
    if (strpos($ofl, '<ENVVERSION>9.3')) { $matv93count++; }
    if (strpos($ofl, '<ENVVERSION>9.4')) { $matv94count++; }
  }

  if (strpos($ofl, '<ENVIRONMENT>Octave')) {
    $isoctave = 1;
  }

  // Operating system:
  if (strpos($ofl, '<OS>Linux')) {
    $assigned++ ;
    $linuxcount++;
    $islinux = 1;

    if (strpos($ofl, '<ENVARCH>GLNXA64') || strpos($ofl, '<ENVARCH>x86_64-pc')) {
      $linux64count++;
    }

    if (strpos($ofl, '<ENVARCH>arm')) {
      $linuxarmcount++;
    }
  }

  if (strpos($ofl, '<OS>Windows')) {
    $assigned++ ;
    $wincount++;
    $iswin = 1;

    if (strpos($ofl, 'Windows-Unknown') || strpos($ofl, 'Version 4.')) { $winunknowncount++; $iswin = 2; }
    if (strpos($ofl, 'Version 5.0 (Build') || strpos($ofl, 'NT-5.0')) { $win2kcount++; $iswin = 2; }
    if (strpos($ofl, 'Version 5.1 (Build') || strpos($ofl, 'Version 5.2 (Build') || strpos($ofl, 'NT-5.1') || strpos($ofl, 'NT-5.2') || strpos($ofl, '<OS>Windows-XP</OS>')) { $winxpcount++; $iswin = 2; }
    if (strpos($ofl, 'Version 6.0 (Build') || strpos($ofl, 'NT-6.0')) { $winvistacount++; $iswin = 2; }
    if (strpos($ofl, 'Version 6.1 (Build') || strpos($ofl, 'NT-6.1')) { $win7count++; $iswin = 2; }
    if (strpos($ofl, 'Version 6.2 (Build') || strpos($ofl, 'NT-6.2')) { $win8count++; $iswin = 2; }
    if (strpos($ofl, 'Version 6.3 (Build') || strpos($ofl, 'NT-6.3')) { $win81count++; $iswin = 2; }
    if (strpos($ofl, 'Version 10.0 (Build') || strpos($ofl, 'NT-10.')) { $win10count++; $iswin = 2; }

    if (($iswin < 2) && ($debugmode > 0)) print "LOGPARSER-WARNING: UNASSIGNED WINDOWS - ID: $ofl <br />";

    if ($ismatlab > 0) {
        // Which Matlab release class on Windows?
        if (strpos($ofl, '(R20')) {
          // Some R200x release:
          $winmatr200xcount++;

          // Take R2006 series into special account:
          if (strpos($ofl, '(R2006')) { $winmatr2006count++; }
        }
        else {
          // Pre R200x series:
          $winmatrothercount++;
        }
    }

    if (strpos($ofl, '<ENVARCH>PCWIN64') || strpos($ofl, '<ENVARCH>i686-pc-mingw64') || strpos($ofl, '<ENVARCH>i686-w64-mingw64')) {
      $win64count++;
    }
  }

  // On MacOS-X by system architecture and type:
  if (strpos($ofl, '<OS>MacOS-X')) {

    $assigned++;
    $osxcount++;
    $isosx = 1;

    if (strpos($ofl, '10.3.')) { $panthercount++; }
    if (strpos($ofl, '10.4.')) { $tigercount++; }
    if (strpos($ofl, '10.5.')) { $leopardcount++; }
    if (strpos($ofl, '10.6.')) { $snowleopardcount++; }
    if (strpos($ofl, '10.7.')) { $lioncount++; }
    if (strpos($ofl, '10.8.')) { $mountainlioncount++; }
    if (strpos($ofl, '10.9.')) { $maverickscount++; }
    if (strpos($ofl, '10.10.')) { $yosemitecount++; }
    if (strpos($ofl, '10.11.')) { $elcapitancount++; }
    if (strpos($ofl, '10.12.')) { $sierracount++; }
    if (strpos($ofl, '10.13.')) { $highsierracount++; }

    if (strpos($ofl, '<CPUARCH>ppc')) {
      // It is a PowerPC Macintosh:
      $ppccount++;
    }
    else {
      // Intel-Mac:
      $intelmaccount++;
    }

    if (strpos($ofl, '<ENVARCH>MACI64') || strpos($ofl, '<ENVARCH>x86_64-apple-darwin')) {
      $osx64count++;
    }
  }

  // Accounting for Octave-3 and later:
  if ($isoctave > 0) {
    if ($islinux > 0) {
        $octavelinuxcount++;
    }

    if ($isosx > 0) {
        $octaveosxcount++;
    }

    if ($iswin > 0) {
        $octavewincount++;
        if (strpos($ofl, '<ENVVERSION>4.')) { $octave4wincount++; }
    }
  }

  if (strpos($ofl, '<ISUPDATE>1')) {
    if (strpos($curdate, '20') || strpos($curdate, '2010') || strpos($curdate, '2011') || strpos($curdate, '2012') || strpos($curdate, '2013') || strpos($curdate, '2014') || strpos($curdate, '2015') || strpos($curdate, '2016') || strpos($curdate, '2017') || strpos($curdate, '2018')) {
      $updatecount++;
    }
  }

  if ($assigned < 2 && $debugmode > 0) print "LOGPARSER-WARNING: UNASSIGNED ID: $ofl <br />";
  if ($assigned > 2 && $debugmode > 0) print "LOGPARSER-WARNING: DOUBLE-ASSIGNED ID: $ofl <br />";
}


// Output the statistics as HTML formatted code: This is fed into the WikkaWikki engine:
print "<h3>";
print "<br /><br />Final count of registered Psychtoolbox-3 installations at " . date ("F d Y H:i:s.", filemtime($filename)) . "<br />";
print "<pre><br />";
print "Total number of downloads + updates     : $transactioncount<br />";

print "<br />";
print "Not counted downloads due to invalid id : $downloaddiscardedcount<br />";
print "Not counted in total due to invalid id  : $discardedcount<br />";
print "Total number of unique installations    : $totalcount<br />";
print "Number of PTB's updated at least once   : $updatecount<br />";

print "<br />Breakdown of installations by Psychtoolbox flavor:<br /><br />";
print "'beta'/'current'              : $betacount<br />";
print "'unsupported' aka 'stable'    : $stablecount<br />";
print "'trunk'                       : $trunkcount<br />";
print "Psychtoolbox-3.0.7            : $oldptb307count<br />";
print "Psychtoolbox-3.0.8-PreTiger   : $ptb308pretigercount<br />";
print "Psychtoolbox-3.0.8            : $ptb308count<br />";
print "Psychtoolbox-3.0.9            : $ptb309count<br />";
print "Psychtoolbox-3.0.10           : $ptb3010count<br />";
print "Psychtoolbox-3.0.11-PreWinGst : $ptb3011oldgscount<br />";
print "Psychtoolbox-3.0.11           : $ptb3011count<br />";
print "Psychtoolbox-3.0.12           : $ptb3012count<br />";
print "Psychtoolbox-3.0.13           : $ptb3013count<br />";
print "Unclassified                  : $unknowncount<br />";

print "<br />Estimated breakdown by host operating system (*):<br /><br />";
printf('MacOS-X all                  : %8d (%7.3f%%) <br />', $osxcount, 100 * $osxcount / $totalcount);
printf('Windows all                  : %8d (%7.3f%%) <br />', $wincount, 100 * $wincount / $totalcount);
printf('Linux all                    : %8d (%7.3f%%) <br />', $linuxcount, 100 * $linuxcount / $totalcount);
printf('Linux on ARM embedded/mobile : %8d <br />', $linuxarmcount);
printf('Linux   64 Bit Matlab/Octave : %8d (%7.3f%% of all Linux systems) <br />', $linux64count, 100 * $linux64count / $linuxcount);
printf('Windows 64 Bit Matlab/Octave : %8d (%7.3f%% of all Windows systems) <br />', $win64count, 100 * $win64count / $wincount);
printf('MacOS-X 64 Bit Matlab/Octave : %8d (%7.3f%% of all MacOS-X systems) <br />', $osx64count, 100 * $osx64count / $osxcount);
print "<br />(*) Strong underestimate for Linux, overestimate for Windows and OSX!<br /><br />";

print "<br />For Macintosh - Breakdown by system architecture:<br /><br />";

printf('PowerMac                     : %4d (%7.3f%% of all Apple systems) <br />', $ppccount, 100 * $ppccount / $osxcount);
print "IntelMac                     : $intelmaccount<br />";

print "<br />For Macintosh - Breakdown by OS/X version:<br /><br />";
print "10.3  - Panther               : $panthercount<br />";
print "10.4  - Tiger                 : $tigercount<br />";
print "10.5  - Leopard               : $leopardcount<br />";
print "10.6  - Snow Leopard          : $snowleopardcount<br />";
print "10.7  - Lion                  : $lioncount<br />";
print "10.8  - Mountain Lion         : $mountainlioncount<br />";
print "10.9  - Mavericks             : $maverickscount<br />";
print "10.10 - Yosemite              : $yosemitecount<br />";
print "10.11 - El Capitan            : $elcapitancount<br />";
print "10.12 - Sierra                : $sierracount<br />";
print "10.13 - High Sierra           : $highsierracount<br />";

print "<br />For MS-Windows - Breakdown by Windows version:<br /><br />";
print "Windows additional preVistas : $winunknowncount<br />";
print "Windows 2000                 : $win2kcount<br />";
print "Windows XP                   : $winxpcount<br />";
print "Windows Vista                : $winvistacount<br />";
print "Windows 7                    : $win7count<br />";
print "Windows 8                    : $win8count<br />";
print "Windows 8.1                  : $win81count<br />";
print "Windows 10                   : $win10count<br />";

print "<br />For MS-Windows - Breakdown by Matlab release:<br /><br />";

$r2007aeqvcount = $winmatr200xcount - $winmatr2006count;
$r11eqvcount = $winmatrothercount + $winmatr2006count;

print "Matlab R2007a (V7.4) or later: $r2007aeqvcount<br />";
print "Previous Matlab releases     : $r11eqvcount<br />";

print "<br />Breakdown for all systems by Matlab major versions:<br /><br />";
print "Matlab 5.x                   : $matv5count<br />";
print "Matlab 6.x                   : $matv6count<br />";
print "Matlab 7.x                   : $matv7count<br />";
print "Matlab 7.0   (R2005a)        : $matv70count<br />";
print "Matlab 7.1   (R2005b)        : $matv71count<br />";
print "Matlab 7.2   (R2006a)        : $matv72count<br />";
print "Matlab 7.3   (R2006b)        : $matv73count<br />";
print "Matlab 7.4   (R2007a)        : $matv74count<br />";
print "Matlab 7.5   (R2007b)        : $matv75count<br />";
print "Matlab 7.6   (R2008a)        : $matv76count<br />";
print "Matlab 7.7   (R2008b)        : $matv77count<br />";
print "Matlab 7.8   (R2009a)        : $matv78count<br />";
print "Matlab 7.9   (R2009b)        : $matv79count<br />";
print "Matlab 7.10  (R2010a)        : $matv710count<br />";
print "Matlab 7.11  (R2010b)        : $matv711count<br />";
print "Matlab 7.12  (R2011a)        : $matv712count<br />";
print "Matlab 7.13  (R2011b)        : $matv713count<br />";
print "Matlab 7.14  (R2012a)        : $matv714count<br />";
print "Matlab 8.0   (R2012b)        : $matv80count<br />";
print "Matlab 8.1   (R2013a)        : $matv81count<br />";
print "Matlab 8.2   (R2013b)        : $matv82count<br />";
print "Matlab 8.3   (R2014a)        : $matv83count<br />";
print "Matlab 8.4   (R2014b)        : $matv84count<br />";
print "Matlab 8.5   (R2015a)        : $matv85count<br />";
print "Matlab 8.6   (R2015b)        : $matv86count<br />";
print "Matlab 9.0   (R2016a)        : $matv90count<br />";
print "Matlab 9.1   (R2016b)        : $matv91count<br />";
print "Matlab 9.2   (R2017a)        : $matv92count<br />";
print "Matlab 9.3   (R2017b)        : $matv93count<br />";
print "Matlab 9.4   (R2018a)        : $matv94count<br />";

print "<br />Number of GNU/Octave V3+ installations by system:<br /><br />";
printf('Octave on OS/X               : %8d (%7.3f%% of all OS/X installs) <br />', $octaveosxcount, 100 * $octaveosxcount / $osxcount);
printf('Octave on Linux              : %8d (%7.3f%% of all Linux installs) <br />', $octavelinuxcount, 100 * $octavelinuxcount / $linuxcount);
printf('Octave all on Windows        : %8d (%7.3f%% of all Windows installs) <br />', $octavewincount, 100 * $octavewincount / $wincount);
printf('Octave 4 on Windows          : %8d (%7.3f%% of all Windows installs) <br />', $octave4wincount, 100 * $octave4wincount / $wincount);

print "</pre></h3><pre>";
print "Parsed lines in registration log        : $linescount<br />";
print "Invalid (skipped) log entries           : $corruptcount<br />";
print "</pre>";

// Done.
?>
