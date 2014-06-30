<?php

/* HTML EXPORT */
namespace MusicParser\Export\PlainStructure{
		
		abstract class SongObject implements \MusicParser\IExport{
				public static function export(&$el){
						$o = get_class($el);
						if($el->children) for($i = 0; $i < count($el->children);$i++) $o .= "\n$indent'-".$el->children[$i]->__toPlainStructure($indent.($i === count($el->children) - 1 ? "  " : "| "));
						else $o .= " (".str_replace("\n","/",mb_substr($el->text,0,20000)).")";
						return $o;	
				}
		}	
}
?>