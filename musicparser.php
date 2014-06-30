<?php

namespace{
		
		class MusicParser{
				
				const regSection = "/\n\<-{2,}\>\s*\n/";
				const regColumn = "/\n-{2,}\>\s*\n/";			
				
				const regVerse = "/^((?:-{2,}\>|\<-{2,})?)(\d+|#)([\.\)])( |$)/m";
				const regChorus = "/^((?:-{2,}\>|\<-{2,})?)((?:R\d*|\(R\d*\)|®|Ref.?|Chorus)\:) /m";
				const regPart = "/^((?:-{2,}\>|\<-{2,})?)([^\s\[]+)(\:)( |$)/m";
				const regBlockTab = "/(^|^\s*\n|^\s*\n\s*\n|\n\s*\n\s*\n)(?=(?:[\w ]+\n)?[A-H]?\:?\|\-)/i";//(.+\n)?(?=$|\n\s*\n)
				const regTextBlock = "/(?:^|\n\s*\n\s*\n)(-{2,}\>|\<-{2,})?/";
				
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
						
						return new Musicparser\Structure\Song(null,$data);

				}

		}
		
}

/* Interfaces */
namespace MusicParser{
		interface IExport{
				static function export(&$el);
		}
}

/* SONG STRUCTURE */
namespace MusicParser\Structure{
		
		abstract class SongObject{
				
				protected $parent;
				public $children = array();
				public $text = "";
				
				public function __construct($parent,$contents){
						$this->parent = $parent;
						if(method_exists($this,"parse")) call_user_func_array(array($this,"parse"),array_slice(func_get_args(),1));
						else $this->text = $contents;
				}
				
				public function __toStructure(){
						$o = array("type" => get_class($this));
						if($this->children) foreach($this->children as $child) $o["children"][] = $child->__toStructure();
						else $o["type"] .= " (".$this->__toString().")";
						return $o;
				}
				
				final public function export($type){
						require_once __DIR__."/musicparser.export.".strtolower($type).".php";
						
						$class = str_replace(__NAMESPACE__."\\","",get_class($this));
						$exporter_class = "\\MusicParser\\Export\\$type\\$class";
						return $exporter_class::export($this);
						
				}
				
				final public function __toHTML(){
						return $this->export("HTML");
				}
				
				final public function __toString(){
						return $this->export("Text");
				}
				
				final public function __toPlainStructure(){
						return $this->export("PlainStructure");
				}
				
				final public function __toTextOnly(){
						return $this->export("TextOnly");
				}
				
				public function getClosest($class = null){
						if(get_class($this) === __NAMESPACE__."\\".$class) return $this;
						if(!$this->parent) return null;
						return $this->parent->getClosest($class);
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
		}
		
		class Section extends SongObject{
				
				public $columns;
				
				public function parse(&$contents){
						$this->columns = &$this->children;
						foreach(preg_split(\MusicParser::regColumn,$contents) as $childContent) $this->children[] = new Column($this,$childContent);
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
				
		}
		
		abstract class SongPart extends SongObject{
				
				public $float;
				public $clear;
				public $number;
				public $delimiter;
				
				public function parse($contents,$matches = array()){
						
						$this->float = (substr(@$matches[1],0,1) === "-");
						$this->clear = (substr(@$matches[1],0,1) === "<");
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
				
		}
		
		class Verse extends SongPart{
				public function parse($contents,$matches){
						parent::parse($contents,$matches);
						if($this->label === "#") $this->label = $this->getClosest("Song")->verseNum + 1;
						$this->getClosest("Song")->verseNum = (int) $this->label;
				}
		}
		class Chorus extends SongPart{}
		class Part extends SongPart{}
		class BlockTab extends SongPart{
				public $precedingBlank;
				public $contents;
				public function parse(&$contents,$matches){
						$this->precedingBlank = $matches[1];
						$this->contents = $contents;
				}

		}
		class TextBlock extends SongPart{}
		
		/* INLINE BLOCKS */
		
		abstract class SongInline extends SongObject{}
		class Text extends SongInline{}
		class InlineTab extends SongInline{}
		class Diagram extends SongInline{
				public function parse($contents,$matches){
						$this->chord = $matches[1];
						$this->template = $matches[2];
				}
		}
		class Chord extends SongInline{
				public $chords;
				public $separate = false;
				public function parse($contents,$matches){
						$this->chords = array_map("trim",explode(",",$matches[1]));
						$this->separate = ($matches[2] === "|");
				}
		}
		class EOL extends SongInline{}

}
?>