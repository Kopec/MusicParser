<?php

/* Text EXPORT */
namespace MusicParser\Export\TextOnly{

		
		abstract class SongObject implements \MusicParser\IExport{
				public static function export(&$el){
						if(!$el->children) return $el->text;
						$o = "";
						foreach($el->children as $child) $o .= $child->__toTextOnly();
						return $o;
				}
		}
		
		class Song extends SongObject{}
		
		class Section extends SongObject{}
		
		class Column extends SongObject{}
		
		abstract class SongPart extends SongObject{}
		
		class Verse extends SongPart{}
		class Chorus extends SongPart{}
		class Part extends SongPart{}
		class BlockTab extends SongPart{}
		class TextBlock extends SongPart{}
		
		/* INLINE BLOCKS */
		abstract class SongInline extends SongObject{}
		
		class Text extends SongInline{}
		class InlineTab extends SongInline{public static function export(&$el){return "";}}
		class Diagram extends SongInline{public static function export(&$el){return "";}}
		class Chord extends SongInline{public static function export(&$el){return "";}}
		class EOL extends SongInline{public static function export(&$el){return "\n";}}

}
?>