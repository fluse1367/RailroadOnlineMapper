<!DOCTYPE html>
<html lang="en">
<?php
    $PageTitle="RailroadsOnlineMapper";
    include_once('includes/head.php');

    // Create required folders if they don't exist
    $folders = array("public", "saves", "uploads");
    foreach ($folders as $folder) {
        if (!file_exists($folder)) {
            mkdir($folder);
        }
    }

    // Create counter if it doesn't exist
    if (!file_exists('counter')) {
        file_put_contents('counter', 0);
    }

    $tableHeader = '<thead>
                        <th>NAME</th>
                        <th><A href="?sortby=0&sortorder=desc" style="color: white">Track Length</A></th>
                        <th><A href="?sortby=1&sortorder=desc" style="color: white">#Y</A> / <A href="?sortby=6&sortorder=desc" style="color: white">#T</A></th>
                        <th><A href="?sortby=2&sortorder=desc" style="color: white">Locos</A></th>
                        <th><A href="?sortby=3&sortorder=desc" style="color: white">Carts</A></th>
                        <th><A href="?sortby=4&sortorder=desc" style="color: white">Slope</A></th>
                    </thead>';

    function mysort($a, $b){
        global $db;
        $x=1;
        if(strtolower($db[substr($a, 5, -5) . '.sav'][$_GET['sortby']]) == strtolower($db[substr($b, 5, -5) . '.sav'][$_GET['sortby']])){
            return 0;
        }
        if(strtolower($db[substr($a, 5, -5) . '.sav'][$_GET['sortby']]) > strtolower($db[substr($b, 5, -5) . '.sav'][$_GET['sortby']])){
            $x=-1;
        } else {
            $x=1;
        }
        if($_GET['sortorder']=='desc'){
            return $x;
        } else {
            return -$x;
        }
    }

?>
<body>
    <header class="header">
        <h1 class="logo">RailroadsOnlineMapper</h1>
        <a class="button" href="upload.php">Upload Savegame</a>
    </header>
    <main>
        <section class="uploads">
            <h2>Latest uploads</h2>
            <div class="uploads__tables">
                <table>
                    <?php
                    echo $tableHeader;
                    $dh = opendir('done/');
                    while ($file = readdir($dh)) {
                        if (substr($file, -5) == '.html') {
                            $files[filemtime('done/' . $file)] = 'done/' . $file;
                        }
                    }
                    if ((isset($files) && $files != null) && file_exists('db.db')) {
                        $db = unserialize(file_get_contents('db.db'));
                        //array($totalTrackLength, $totalSwitches, $totalLocos, $totalCarts, $maxSlope);

                        if (!isset($_GET['sortby']) || !isset($_GET['sortorder'])) {
                            krsort($files);
                        } else {
                            usort($files, 'mysort');
                        }

                        $hard_limit = 1600;
                        $soft_limit = 800;
                        for ($i = 0; $i < sizeof($files); $i++) {
                            $file = array_shift($files);
                            if (!$file) break;

                            if ($i > $hard_limit) {
                                unlink("done/" . substr($file, 5, -5) . ".html");
                            }

                            if ($i >= $soft_limit) {
                                continue;
                            }

                            $dl = '';
                            if (file_exists('public/' . substr($file, 5, -5) . '.sav')) {
                                $dl = ' (DL)';
                            }

                            echo '<tr><td><a href="done/' . substr($file, 5, -5) . '.html?t=' . time() . '">' . substr($file, 5, -5) . $dl . '</a></td>
                                <td>' . round($db[substr($file, 5, -5) . '.sav'][0] / 100000, 2) . 'km</td>
                                <td>' . $db[substr($file, 5, -5) . '.sav'][1] . ' / ' . $db[substr($file, 5, -5) . '.sav'][6] . '</td>
                                <td>' . $db[substr($file, 5, -5) . '.sav'][2] . '</td>
                                <td>' . $db[substr($file, 5, -5) . '.sav'][3] . '</td>
                                <td >' . round($db[substr($file, 5, -5) . '.sav'][4]) . '%</td>
                                </tr>';
                            if (!(($i + 1) % 15)) {
                                if (($i + 1) < $soft_limit) {
                                    echo '</table><table>' . $tableHeader;
                                }
                            }

                        }
                    }
                    echo '</table>';
                    ?>
                </table>
            </div>
        </section>
    </main>
    <?php include_once('includes/footer.php') ?>
</body>
</html>
<?php
$dir = 'saves';
$dh = opendir($dir);

while($file=readdir($dh)){
    if($file && (substr($file,-4) == '.sav' || substr($file,-13) == '.sav.modified')){
        if(filemtime($dir.'/'.$file)<time()-600){
            unlink($dir.'/'.$file);
        }
    }
}