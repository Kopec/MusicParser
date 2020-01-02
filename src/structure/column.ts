import { SongPart } from "../schema";
import { Verse } from "./verse";

export class Column extends SongPart {

  re_verse = /^(?:(?:(\d+|#)(\.)|(\w+)(\:))(?:\n|))/m;

  verses: Verse[];

  constructor(source: string) {
    super(source);
    const sourceParts = source.trim().split(this.re_verse);

    this.verses = this.createVerses(sourceParts);
  }

  createVerses(sourceParts: string[]): Verse[] {
    const verses: Verse[] = [];

    if (sourceParts.length) {
      const source = <string>sourceParts.shift(); // cannot be undefined is length > 0
      if (source.trim().length > 0) verses.push(new Verse(source, null, null));
    }

    while (sourceParts.length) {
      const [label1, separator1, label2, separator2, source] = sourceParts.splice(0, 5);
      verses.push(new Verse(source, label1 || label2, separator1 || separator2));
    }

    return verses;
  }


  getName() {
    return "Column";
  }

  getChildren() {
    return this.verses;
  }
}