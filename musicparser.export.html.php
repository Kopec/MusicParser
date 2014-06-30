<?php

/* HTML EXPORT */
namespace MusicParser\Export\HTML{
		
		abstract class SongObject implements \MusicParser\IExport{
				public static function export(&$el){
						if(!$el->children) return nl2br($el->text,false);
						$o = "";
						foreach($el->children as $child) $o .= $child->__toHTML();
						return $o;
				}
		}
		
		class Song extends SongObject{
				
				public static function export(&$el){
						return "<div class=\"song\">".parent::export($el)."</div>";
				}
		}
		
		class Section extends SongObject{
				public static function export(&$el){
						return "<div class=\"section\">".parent::export($el)."</div>";
				}
		}
		
		class Column extends SongObject{
				public static function export(&$el){
						$width = floor(100/count($el->getClosest("Section")->columns));
						return "<div class=\"column\" style=\"width:$width%;\">".parent::export($el)."</div>";
				}
				
		}
		
		abstract class SongPart extends SongObject{

				protected static function mergeChildrenHTML(&$el){
						$o = "";
						$i = 0;
						$only_chords = null;
						$line_start = true;
						$children = &$el->children;
						
						while(@$children[$i]){
								
								$class = end(explode("\\",get_class($children[$i])));
								
								switch($class){
										
										case "Chord":
										case "Text":
																
										$text = "";
										$chord = "";
										$separate = false;
										
										if($only_chords === null){
												$a = $i;
												while(@$children[$a] instanceof \MusicParser\Structure\Chord) $a++;
												$only_chords = (!isset($children[$a]) || ($children[$a] instanceof \MusicParser\Structure\EOL));
										}
										
										if($children[$i] instanceof \MusicParser\Structure\Chord){
												$chord .= $children[$i]->__toHTML();
												if(!$only_chords) $chord .= "<br>";
												$separate = $children[$i]->separate;
												$i++;
										}
										
										if(!$separate) while(@$children[$i] && $children[$i] instanceof \MusicParser\Structure\Text){$text .= $children[$i]->__toHTML();$i++;}
										
										if($line_start) $text = ltrim($text);
										
										if(!$text && $chord && !$only_chords) $text = "&nbsp;";
										$text = preg_replace("/(^ | $)/","&nbsp;",$text); // preceding and trailing space does not render at the end of inline-block
										
										$line_start = false;
										
										$o .= "<span class=\"linepart\">{$chord}{$text}</span>";
										break;
										
										case "EOL":
										$only_chords = null;
										$line_start = true;
										
										default:
										$o .= $children[$i]->__toHTML();
										$i++;
								}
								
								
						}
						
						unset($text,$chord);
						return $o;
				}
				
				public static function export(&$el){
						$classes = array("part",strtolower(end(explode("\\",get_class($el)))));
						if($el->float) $classes[] = "float";
						if($el->clear) $classes[] = "clear";
						$label = $el->label ? "<span class=\"label\"><span>".$el->label.$el->delimiter."</span>&nbsp;</span>" : "";
						return "<div class=\"".join(" ",$classes)."\">".$label.self::mergeChildrenHTML($el)."</div>";
				}
				
		}
		
		class Verse extends SongPart{}
		class Chorus extends SongPart{}
		class Part extends SongPart{}
		class BlockTab extends SongPart{
				public static function export(&$el){
						return nl2br($el->precedingBlank."<span class=\"tab block\">".str_replace(" ","&nbsp;",$el->contents)."</span>",false);
				}
		}
		class TextBlock extends SongPart{
				public static function export(&$el){					
						$classes = array("part","textblock");
						if($el->float) $classes[] = "float";
						if($el->clear) $classes[] = "clear";
						return "<div class=\"".join(" ",$classes)."\">".self::mergeChildrenHTML($el)."</div>";
				}
		}
		
		/* INLINE BLOCKS */
		
		abstract class SongInline extends SongObject{}
		class Text extends SongInline{
				public static function export(&$el){
						$text = $el->text;
						
						/* stylisticke upravy */
						$text = preg_replace("/(?<![\w])\"(?=\w)/","&bdquo;",$text);
						$text = preg_replace("/(?<=[\.,\w])\"(?!\w)/","&rdquo;",$text);
						$text = preg_replace("/\.{3}/","&hellip;",$text);
						$text = preg_replace("/(^|[\W])(\d+)x(?=(\W|$))/","$1$2&times;",$text);
						return $text;
				}
		}
		class InlineTab extends SongInline{
				public static function export(&$el){
						return "<span class=\"tab inline\">".str_replace(" ","&nbsp;",parent::export($el))."</span>";
				}
		}
		class Diagram extends SongInline{
				public static function export(&$el){
						$chord = $el->chord;
						$template = $el->template;
						$size = 100;
						ob_start();
						include __DIR__."/diagram-svg.php";
						return "<span class=\"diagram\">".ob_get_clean()."</span>";
				}
		}
		class Chord extends SongInline{
				public static function export(&$el){
						$chords = "";
						foreach($el->chords as $chord) $chords .= "<span>$chord</span>&nbsp;";
						return "<span class=\"chord\">$chords</span>";
				}
		}
		class EOL extends SongInline{
				public static function export(&$el){
						return "<br>";
				}
		}
		
}
?>