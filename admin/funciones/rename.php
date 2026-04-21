<?php
	require_once('db.php');

	mysqli_select_db($connectFX, $databaseFX);
	$query_productos = "SELECT * FROM $vista_prod WHERE ENVIA_SITE = 'S' AND NOMPRO NOT LIKE 'ZZ%' AND NOMPRO NOT LIKE 'MKT%' AND NOMPRO NOT LIKE 'ASIST%' AND NOMPRO NOT LIKE 'RMA%' AND NOMPRO NOT LIKE 'FOB%' AND NOMPRO NOT LIKE 'FX%' AND NOMPRO NOT LIKE 'REMERA%' AND NOMPRO NOT LIKE 'DEMO%' AND NOMPRO <> 'IVA' AND NOMPRO <> 'FLETE' ";
	$productos = mysqli_query($connectFX, $query_productos) or die(mysqli_error($link));
	$row_productos = mysqli_fetch_assoc($productos);
	$totalRows_productos = mysqli_num_rows($productos);
	$i=0;
	do {

		$nombre_ficheroPD= "../documentos/".$row_productos['REFERENCIA'].".png";

		if (file_exists($nombre_ficheroPD) == "1"){
			echo $row_productos['CODWEB']; echo "------"; echo $row_productos['NOMPRO']; echo "<br>";
			$i++;
		}


		$row_productos = mysqli_fetch_assoc($productos);
	} while ($row_productos);  

	echo $i;
	 //end 
/*$ruta;
$doc=$ruta."text.txt";
$doc2=$ruta."test2.txt";

rename ($doc, $doc2);*/

?>