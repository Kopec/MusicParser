<?php

/* Text EXPORT */
namespace MusicParser\Export\Text{
		
		const SONGPART_MARGIN = 6;
		
		abstract class SongObject implements \MusicParser\IExport{
				public static function export(&$el){
						if(!$el->children) return $el->text;
						$o = "";
						foreach($el->children as $child) $o .= $child->__toString();
						return $o;
				}
		}
		
		class Song extends SongObject{
		}
		
		class Section extends SongObject{}
		
		class Column extends SongObject{
				public static function export(&$el){
						//!!! $width = floor(100/count($el->getClosest("Section")->columns));
						return join("\n\n",$el->children);
				}
		}
		
		abstract class SongPart extends SongObject{
				
				public static function export(&$el){
						$i = 0;
						$children = &$el->children;
						$firstline = true;
						
						$lines = array();

						while(@$children[$i]){
								
								$text = "";
								$chords = "";
								
								if($firstline){
										$text .= str_repeat(" ",SONGPART_MARGIN - mb_strlen($el->label.$el->delimiter));
										$text .= $el->label.$el->delimiter." ";
										$firstline = false;
								}else{
										$text .= str_repeat(" ",SONGPART_MARGIN)." ";
								}								
								
								while(@$children[$i]){
										if($children[$i] instanceof \MusicParser\Structure\Chord){
												if(mb_strlen($chords) < mb_strlen($text)){
														$chords .= str_repeat(" ",mb_strlen($text) - mb_strlen($chords));
														$chords .= $children[$i]->__toString();
												}
												else{
														$text .= str_repeat(" ",mb_strlen($chords) - mb_strlen($text) + 1);
														$chords .= " ".$children[$i]->__toString();
												}
										}
										
										if($children[$i] instanceof \MusicParser\Structure\Text){
												$text .= $children[$i]->__toString();
										}
										
										if($children[$i] instanceof \MusicParser\Structure\EOL){
												$i++;
												break;
										}
										
										$i++;
										
								}
								
								if(trim($chords)) $lines[] = $chords;
								$lines[] = $text;
								
								
						}
						
						return join("\n",$lines);
				}
		}
		
		class Verse extends SongPart{}
		class Chorus extends SongPart{}
		class Part extends SongPart{}
		class BlockTab extends SongPart{}
		class TextBlock extends SongPart{}
		
		/* INLINE BLOCKS */
		abstract class SongInline extends SongObject{}
		class Text extends SongInline{}
		class InlineTab extends SongInline{}
		class Diagram extends SongInline{
				/*public static function export(&$el){
						$chord = $el->chord;
						$template = $el->template;
						$size = 100;
						ob_start();
						include __DIR__."/diagram-svg.php";
						return ob_get_clean();
				}*/
		}
		class Chord extends SongInline{
				public static function export(&$el){
						return join(" ",$el->chords);
				}
		}
		class EOL extends SongInline{
				public static function export(&$el){
						return "\n";
				}
		}
		
}
?>