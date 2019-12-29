import { SongPart } from "../schema";
import { Verse } from "./verse";

export class Column implements SongPart {

  re_verse = /^(?:(?:(\d+|#)\.|(\w+)\:)(?:\n|))/m;

  verses: Verse[];

  constructor(public source: string) {
    const sourceParts = source.trim().split(this.re_verse);

    this.verses = this.createVerses(sourceParts);
  }

  createVerses(sourceParts: string[]): Verse[] {
    const verses: Verse[] = [];

    if (sourceParts.length) {
      const source = <string>sourceParts.shift(); // cannot be undefined is length > 0
      if (source.trim().length > 0) verses.push(new Verse(source, null));
    }

    while (sourceParts.length) {
      const [label1, label2, source] = sourceParts.splice(0, 3);
      verses.push(new Verse(source, label1 || label2));
    }

    return verses;
  }

  getChildren() {
    return this.verses;
  }
}