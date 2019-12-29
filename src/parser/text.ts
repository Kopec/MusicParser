import { SongPart } from "../schema";
import { Chord } from "./chord";
import { TextPart } from "./textpart";
import { Diagram } from "./diagram";

export class Text implements SongPart {

  re_chord = /\[[^\[\]\n]+?\]/;
  re_diagram = /\[\[[^ ]+ \d{6}\]\]/;

  children: SongPart[] = [];

  constructor(public source: string) {

    this.children = this.parseText(source);
  }

  parseText(source: string): SongPart[] {

    const children: SongPart[] = [];

    const regexps = [this.re_chord, this.re_diagram];
    const regexpMerged = new RegExp(`(?:${regexps.map(reg => `(${reg.source})`).join("|")})`, "gm");

    let match: RegExpExecArray | null;
    let lastIndex: number = 0;

    while (match = regexpMerged.exec(source)) {
      const [partSource, chord, diagram] = match;

      // preceding text
      if (source.substring(lastIndex, match.index)) children.push(new TextPart(source.substring(lastIndex, match.index)));

      // current token
      if (chord) children.push(new Chord(partSource));
      if (diagram) children.push(new Diagram(partSource));

      lastIndex = match.index + partSource.length;
    }

    // add remaining text
    if (source.substring(lastIndex)) children.push(new TextPart(source.substring(lastIndex)));

    return children;
  }


  getChildren() {
    return this.children;
  }

}