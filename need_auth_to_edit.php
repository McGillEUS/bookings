<?php
/**
 * Created by PhpStorm.
 * User: Lou
 * Date: 2017-02-02
 * Time: 8:38 PM
 */

require "defaultincludes.inc";

print_header($day, $month, $year, $area, isset($room) ? $room : "");

echo "<p align=\"center\"><br>Unfortunately the account you logged into was not 
                            the account associated with this booking. To resolve this problem,
                            log out using this button below. <br><br>";

echo "<a href=\"http://" . $_SERVER[HTTP_HOST] . "/google_logout.php\"><img src=\"/images/gsignout.png\" style='width:105px;height:35px;'></a></p>";