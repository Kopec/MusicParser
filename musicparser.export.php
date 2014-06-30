<?php

namespace{
		
		class MusicParser{
				
				const regSection = "/\n&lt;-{2,}&gt;\s*\n/";
				const regColumn = "/\n-{2,}&gt;\s*\n/";			
				
				const regVerse = "/^((?:-{2,}&gt;)?)(\d+|#)([\.\)])( |$)/m";
				const regChorus = "/^((?:-{2,}&gt;)?)((?:R\d*|\(R\d*\)|®|Ref.?|Chorus)\:) /m";
				const regPart = "/^((?:-{2,}&gt;)?)([^\s\[]+)(\:)( |$)/m";
				const regBlockTab = "/(^|^\s*\n|^\s*\n\s*\n|\n\s*\n\s*\n)(?=(?:[\w ]+\n)?[A-H]?\:?\|\-)/i";//(.+\n)?(?=$|\n\s*\n)
				const regTextBlock = "/(?:^|\n\s*\n\s*\n)(-{2,}&gt;)?/";
				
				const regChord = "/(?<!\[)\[([A-H][^\]\|]*)(\|?)\]/i";
				const regDiagram = "/\[\[([^\] ]+)( ([xX\d]{0,6}))?\]\]/";
				const regInlineTab = "/(?<=\s)[A-H ]?\:?\|.+(?=\s)/i";
				const regEOL = "/\n/";
				
				const regLineStart = "/(^|\n)()([^\[]+)(?=\[)/i";
				const regLinePart = "/()(\[[A-H][^\]]*\])([^\[\n]*)(?=\[|\n|$)/i";
				
				public static function mergeSearch($search){
						$parts = array();
						foreach($search as $type => $matches){
								foreach($matches as $item){
										if(isset($parts[$item[0][1]])) continue;
										$parts[$item[0][1]]= array("offset" => $item[0][1],"type" => $type);
										foreach($item as $pattern) $parts[$item[0][1]]["matches"][] = $pattern[0];
								}
						}
						
						usort($parts,function($a,$b){return $a["offset"]>$b["offset"] ? 1 : -1;});
						
						return $parts;
				}
				
				public static function parse($data){
						
						/* Normalizace koncu radku */
						$data = str_replace("\r\n","\n",$data);
						
						return new Musicparser\Song(null,$data);

				}
				
				public static function toHTML($data){
						$data = htmlspecialchars($data);
						
						/* Stylisticke upravy */
						$data = preg_replace("/\"(.+)\"/s","&bdquo;$1&rdquo;",$data);
						$data = preg_replace("/\.{3}/","&hellip;",$data);
						$data = preg_replace("/(\d+)x(?=\W)/","$1&times;",$data);
						
						/* parse into Song object */
						$song = self::parse($data);
						
						//\Lethe::dump($song->__toPlainStructure());
						
						return $song->__toHTML();
				}
				
				
				
				
		}
		
}

/* SONG STRUCTURE */

namespace MusicParser{
		
		abstract class SongObject{
				
				protected $parent;
				protected $children = array();
				protected $text = "";
				
				public function __construct($parent,$contents){
						$this->parent = $parent;
						if(method_exists($this,"parse")) call_user_func_array(array($this,"parse"),array_slice(func_get_args(),1));
						else $this->text = $contents;
				}
				
				public function __toString(){return $this->text;}
				
				public function __toPlainStructure($indent = ""){
						$o = get_class($this);
						if($this->children) for($i = 0; $i < count($this->children);$i++) $o .= "\n$indent'-".$this->children[$i]->__toPlainStructure($indent.($i === count($this->children) - 1 ? "  " : "| "));
						else $o .= " (".str_replace("\n","/",mb_substr($this->text,0,20000)).")";
						return $o;						
				}
				
				public function __toStructure(){
						$o = array("type" => get_class($this));
						if($this->children) foreach($this->children as $child) $o["children"][] = $child->__toStructure();
						else $o["type"] .= " (".$this->__toString().")";
						return $o;
				}
				
				public function __toHTML(){
						if(!$this->children) return nl2br($this->text,false);
						$o = "";
						foreach($this->children as $child) $o .= $child->__toHTML();
						return $o;
				}
				
				public function getParent($class){
						if(get_class($this) === __NAMESPACE__."\\".$class) return $this;
						if(!$this->parent) return null;
						return $this->parent->getParent($class);
				}
		}
		
		class Song extends SongObject{
				
				public $sections;
				public $verseNum = 0;
				
				public function parse(&$contents){
						foreach(preg_split(\MusicParser::regSection,$contents) as $childContent) $this->children[] = new Section($this,$childContent);
						//\Lethe::dump($this->children);
						$this->sections = &$this->children;
				}
				
				public function __toHTML(){
						return "<div class=\"song\">".parent::__toHTML()."</div>";
				}
		}
		
		class Section extends SongObject{
				
				public $columns;
				
				public function parse(&$contents){
						$this->columns = &$this->children;
						foreach(preg_split(\MusicParser::regColumn,$contents) as $childContent) $this->children[] = new Column($this,$childContent);
				}
				
				public function __toHTML(){
						return "<div class=\"section\">".parent::__toHTML()."</div>";
				}
		}
		
		class Column extends SongObject{
				public $verses;
				
				public function parse(&$contents){
						
						preg_match_all(\MusicParser::regVerse,$contents,$search["verse"],PREG_OFFSET_CAPTURE | PREG_SET_ORDER);
						preg_match_all(\MusicParser::regChorus,$contents,$search["chorus"],PREG_OFFSET_CAPTURE | PREG_SET_ORDER);
						preg_match_all(\MusicParser::regPart,$contents,$search["part"],PREG_OFFSET_CAPTURE | PREG_SET_ORDER);
						preg_match_all(\MusicParser::regBlockTab,$contents,$search["blocktab"],PREG_OFFSET_CAPTURE | PREG_SET_ORDER);
						preg_match_all(\MusicParser::regTextBlock,$contents,$search["textblock"],PREG_OFFSET_CAPTURE | PREG_SET_ORDER);
						
						$parts = \MusicParser::mergeSearch($search);
						
						for($i = 0;$i < count($parts);$i++){

								$start = $parts[$i]["offset"] + strlen($parts[$i]["matches"][0]);
								$length = @$parts[$i+1]["offset"] ? $parts[$i+1]["offset"] - $start : null;
								$part_content = $length ? substr($contents,$start,$length) : substr($contents,$start);
								$matches = @$parts[$i]["matches"];
								
								switch($parts[$i]["type"]){
										
										case "verse":
										$this->children[] = new Verse($this,$part_content,$matches);
										break;
										
										case "chorus":
										$this->children[] = new Chorus($this,$part_content,$matches);
										break;
										
										case "part":
										$this->children[] = new Part($this,$part_content,$matches);
										break;
										
										case "blocktab":
										$this->children[] = new BlockTab($this,$part_content,$matches);
										break;
										
										case "textblock":
										$this->children[] = new TextBlock($this,$part_content,$matches);
										break;
										
								}
							
						}
						
						
				}
				
				public function __toHTML(){
						$width = floor(100/count($this->getParent("Section")->columns));
						return "<div class=\"column\" style=\"width:$width%;\">".parent::__toHTML()."</div>";
				}
				
		}
		
		abstract class SongPart extends SongObject{
				
				public $float;
				public $number;
				public $delimiter;
				
				public function parse($contents,$matches = array()){
						
						$this->float = (bool) @$matches[1];
						$this->label = @$matches[2];
						$this->delimiter = @$matches[3];
						
						preg_match_all(\MusicParser::regChord,$contents,$search["chord"],PREG_OFFSET_CAPTURE | PREG_SET_ORDER);
						preg_match_all(\MusicParser::regInlineTab,$contents,$search["inlinetab"],PREG_OFFSET_CAPTURE | PREG_SET_ORDER);
						preg_match_all(\MusicParser::regDiagram,$contents,$search["diagram"],PREG_OFFSET_CAPTURE | PREG_SET_ORDER);
						preg_match_all(\MusicParser::regEOL,$contents,$search["eol"],PREG_OFFSET_CAPTURE | PREG_SET_ORDER);
						
						$parts = \MusicParser::mergeSearch($search);
						
						$offset = 0;
						for($i = 0;$i < count($parts);$i++){
								
								$text = substr($contents,$offset,$parts[$i]["offset"] - $offset);
								if($text) $this->children[] = new Text($this,$text);

								$matches = @$parts[$i]["matches"];
								
								switch($parts[$i]["type"]){
										
										case "chord":
										$this->children[] = new Chord($this,$matches[0],$matches);
										break;
										
										case "inlinetab":
										$this->children[] = new InlineTab($this,$matches[0],$matches);
										break;
										
										case "diagram":
										$this->children[] = new Diagram($this,$matches[0],$matches);
										break;
										
										case "eol":
										$this->children[] = new EOL($this,$matches[0]);
										break;
										
								}
								
								$offset = $parts[$i]["offset"] + strlen($matches[0]);
							
						}
						$text = substr($contents,$offset);
						if($text) $this->children[] = new Text($this,$text);
				}
				
				public function mergeChildrenHTML(){
						$o = "";
						$i = 0;
						$only_chords = null;
						$line_start = true;
						$children = &$this->children;
						
						while(@$children[$i]){
								
								$class = str_replace(__NAMESPACE__."\\","",get_class($children[$i]));
								
								switch($class){
										
										case "Chord":
										case "Text":
																
										$text = "";
										$chord = "";
										$separate = false;
										
										if($only_chords === null){
												$a = $i;
												while($children[$a] instanceof Chord) $a++;
												$only_chords = ($children[$a] instanceof EOL);
										}
										
										if($children[$i] instanceof Chord){
												$chord .= $children[$i]->__toHTML();
												if(!$only_chords) $chord .= "<br>";
												$separate = $children[$i]->separate;
												$i++;
										}
										
										if(!$separate) while(@$children[$i] && $children[$i] instanceof Text){$text .= $children[$i]->__toHTML();$i++;}
										
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
				
				public function __toHTML(){
						$classes = array("part",strtolower(str_replace(__NAMESPACE__."\\","",get_class($this))));
						if($this->float) $classes[] = "float";
						$label = $this->label ? "<span class=\"label\"><span>".$this->label.$this->delimiter."</span>&nbsp;</span>" : "";
						return "<div class=\"".join(" ",$classes)."\">".$label.$this->mergeChildrenHTML()."</div>";
				}
				
		}
		
		class Verse extends SongPart{
				public function parse($contents,$matches){
						parent::parse($contents,$matches);
						if($this->label === "#") $this->label = $this->getParent("Song")->verseNum + 1;
						$this->getParent("Song")->verseNum = (int) $this->label;
				}
		}
		class Chorus extends SongPart{}
		class Part extends SongPart{}
		class BlockTab extends SongPart{
				private $precedingBlank;
				private $contents;
				public function parse(&$contents,$matches){
						$this->precedingBlank = $matches[1];
						$this->contents = $contents;
				}
				public function __toHTML(){
						return nl2br($this->precedingBlank."<span class=\"tab block\">".str_replace(" ","&nbsp;",$this->contents)."</span>",false);
				}
		}
		class TextBlock extends SongPart{
				public function __toHTML(){					
						$classes = array("part","textblock");
						if($this->float) $classes[] = "float";
						return "<div class=\"".join(" ",$classes)."\">".$this->mergeChildrenHTML()."</div>";
				}
		}
		
		/* INLINE BLOCKS */
		
		abstract class SongInline extends SongObject{
		}
		class Text extends SongInline{}
		class InlineTab extends SongInline{
				public function __toHTML(){
						return "<span class=\"tab inline\">".str_replace(" ","&nbsp;",parent::__toHTML())."</span>";
				}
		}
		class Diagram extends SongInline{
				public function parse($contents,$matches){
						$this->chord = $matches[1];
						$this->template = $matches[2];
				}
				public function __toHTML(){
						$chord = $this->chord;
						$template = $this->template;
						$size = 100;
						ob_start();
						include __DIR__."/diagram-svg.php";
						return ob_get_clean();
				}
		}
		class Chord extends SongInline{
				public $chords;
				public $separate = false;
				public function parse($contents,$matches){
						$this->chords = array_map("trim",explode(",",$matches[1]));
						$this->separate = ($matches[2] === "|");
				}
				public function __toHTML(){
						$chords = "";
						foreach($this->chords as $chord) $chords .= "<span>$chord</span>&nbsp;";
						return "<span class=\"chord\">$chords</span>";
				}
		}
		class EOL extends SongInline{

				public function __toHTML(){
						return "<br>";
				}
		}
		
		
		
		
		
		
}
?>