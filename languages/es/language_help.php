<?php
echo '<div class="help_box">';
echo '
<p class="help_header">Iconos</p>
<p class="help_text">-Iconos que encontraras en los reportes genealogicos<br><br>
<span class="help_explanation"><img src="'.CMS_ROOTPATH.'images/ancestor_report.gif" alt="Pedigree">&nbsp;<b>Pedigree (Reporte de ancestros)</b>: Un reporte Pedigree es un reporte genealogico de una persona (la persona base) con sus ancestros. Un reporte Pedigree usa un metodo especial de numeracion: la persona base es la numero 1, su padre el numero 2 y su madre la numero 3. El numero de un padre es siempre el doble que el de su hijo y el de una madre es siempre el doble que el del hijo mas 1. Por lo tanto, el numero 40 es el padre del numero 20 y el 41 la madre del numero 20. Puedes leer mas acerca de pedigrees <a href="http://en.wikipedia.org/wiki/Pedigree_chart" target="blank"><b>aqui</b></a> y <a href="http://en.wikipedia.org/wiki/Ahnentafel" target="blank"><b>aqui.</b></a></span><br><br>
<span class="help_explanation"><img src="'.CMS_ROOTPATH.'images/descendant.gif" alt="Parenteel">&nbsp;<b>Reporte de descendientes</b>: Un reporte de descendientes es un reporte genealogico de un patriarca, con o sin su pareja, (generacion I) con sus hijos (generacion II) y todos los demas descendientes, a lo largo de ambas lineas, masculina y femenina. </span><br><br>
<span class="help_explanation"><img src="'.CMS_ROOTPATH.'images/outline.gif" alt="Outline report">&nbsp;<b>Reporte de bosquejo</b>: Un reporte de bosquejo es un sumario claro de todos los descendientes de una persona (y sus parejas), donde cada generacion recibe su propio numero (ascendente). </span><br><br>
<span class="help_explanation"><img src="'.CMS_ROOTPATH.'images/fanchart.gif" alt="Fanchart">&nbsp;<b>Grafica de abanico</b>: Una grafica de abanico es una grafica circular que muestra los ancestros en circulos alrededor de una persona base. Esto permite una vista muy clara de la ascendencia de una persona especifica. La caja de cada persona en la grafica puede ser clicada para tener rapido acceso a la hoja familiar de esa persona. <br> El tamaño del abanico y algunas otras caracteristicas se pueden configurar desde el menu a la izquierda de la grafica. </span><br>
</p>';
echo '</div>';

echo '<p><div class="help_box">';
echo '
<p class="help_header">Menu superior</p>
<p class="help_text">-Items en el menu superior:<br>
-Notaras los botones A+ A- Reset<br>
<span class="help_explanation">Con estos botones podras ajustar el tamaño del texto en la mayoria de las paginas. Con el boton Reset puedes regresar de manera rapida al display por default. (Nota: Reset solo esta visible en browsers que soportan esta funcion). </span><br>
-Tambien notaras un icono RSS de color naranja<br>
<span class="help_explanation">Si añades este feed (como es llamado) a tu lector RSS, podras saber, de un vistazo, quien cumple años!<br>(Tambien en el menu superior principal, en la pestaña EXTRAS, encontraras una pestaña LISTA DE CUMPLEAÑOS. Cuando clicas en ella, se presenta una lista con los cumpleaños del mes).</span><br>
</p>';
echo '</div>';

echo '<p><div class="help_box">';
echo '<p class="help_header">Menu "A/Z"</p>
<p class="help_text">-Idiomas<br>
<span class="help_explanation">En la barra bajo el nombre del sitio veras 4 botones que permiten cambiar el idioma de la pagina.</span><br>
-Campos de busqueda<br>
<span class="help_explanation">En los campos de busqueda puedes buscar personas por nombre y/o apellido. Tambien puedes escoger entre tres opciones: "contiene", "igual a" y "comienza con". Nota: al lado del boton BUSCAR, hay una opcion de  &quot;Busqueda Avanzada!&quot;</span><br>
-Propietario del arbol:<br>
<span class="help_explanation">Al clicar aqui, seras redirigido a un formato de correo del que podras enviarme un mensaje directo. Si deseas enviarme un archivo adjunto (como una foto o un documento) puedes pedirme mi direccion de email a traves de este formulario. (Si publico mi direccion aqui, recibiria mucho Spam...) </span><br>
-Mas<br>
<span class="help_explanation">Las siguientes lineas son obvias.</span><br>
</p>';
echo '</div>';

echo '<p><div class="help_box">';
echo '<p class="help_header">Pestaña "Personas"</p>
<p class="help_text">-Esta pagina presenta una lista de personas<br>
<span class="help_explanation">Aqui puedes ver todas las personas del arbol, ordenadas alfabeticamente. Se presenta un maximo de 150 personas. Presiona el numero de pagina para avanzar.</span><br>
-Vista compacta o expandida<br>
<span class="help_explanation">Tambien notaras un boton &quot;Vista compacta&quot; o un boton &quot;Vista expandida&quot;. En la vista expandida, tambien se muestran las ex parejas, que no se muestran en la vista compacta.</span><br>
</p>';
echo '</div>';

echo '<p><div class="help_box">';
echo '<p class="help_header">Pestaña "Nombres"</p>
<p class="help_text">-Explicacion:<br>
<span class="help_explanation">Aqui encontraras una lista con todos los apellidos, seguido del numero de personas que llevan ese apellido.</span><br>
</p>';
echo '</div>';

echo '<p><div class="help_box">';
echo '<p class="help_header">Pestaña "Lugares"</p>
<p class="help_text">-Explicacion:<br>
<span class="help_explanation">Aqui puedes buscar por lugar de nacimiento, por direccion y lugar de muerte o sepultura. <br>
Puedes escoger entre: "contiene", "igual a" y "comienza con". <br>
Puedes escoger entre vista Compacta y Expandida. <br>
Los resultados seran ordenados alfabeticamente por nombre de lugar.</span><br>
</p>';
echo '</div>';

echo '<p><div class="help_box">';
echo '<p class="help_header">Pestaña "Album de fotos"</p>
<p class="help_text">-Explicacion:<br>
<span class="help_explanation">Aqui veras todas las fotos en la base de datos.<br>Clica en una foto para una version amplificada o clica en el nombre para moverte a la pagina familiar de esa persona.<br>
</p>';
echo '</div>';

echo '<p><div class="help_box">';
echo '<p class="help_header">Pestaña "Extras"</p>
<p class="help_text">-Explicacion:<br>
<span class="help_explanation">Aqui encontraras una serie de herramientas extra.<br>
</p>';
echo '</div>';
?>
