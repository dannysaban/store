<?php
if( !defined( '_VALID_MOS' ) && !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' ); 

foreach ($payments as $html)
{
  echo '<div style="clear: both;">'; 
  echo $html;
  echo '</div>'; 
}