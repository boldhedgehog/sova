<?php
function br2nl($string){
  $return=eregi_replace('<br[[:space:]]*/?'.
    '[[:space:]]*>',chr(13).chr(10),$string);
  return $return;
}