<?php
$c = mysqli_connect('db', 'webadmin', 's2m2p.m2st3r', 'web_samap') or die('conn fail');
mysqli_set_charset($c, 'utf8');
$r = mysqli_query($c, "SELECT * FROM tbl_slider WHERE deleted_at IS NULL") or die('query fail');
echo "type=" . gettype($r) . "\n";
echo "is_mysqli_result=" . ($r instanceof mysqli_result ? 'yes' : 'no') . "\n";
echo "num_rows=" . mysqli_num_rows($r) . "\n";
mysqli_data_seek($r, 0);
while ($row = mysqli_fetch_assoc($r)) {
    echo $row['id'] . ' - ' . $row['nombre'] . "\n";
}
