import { SongPart } from "../schema";
import { Chord } from "./chord";

export class ChordGroup extends SongPart {

  re_chords = /^\[([^\|]+)(\|?)\]$/;

  chords: Chord[];

  separator: boolean;

  constructor(source: string) {
    super(source);

    const match = this.re_chords.exec(source);

    if (!match) {
      this.chords = [];
      this.separator = false;
    }
    else {

      this.chords = match[1]
        .split(",")
        .map(chordSource => new Chord(chordSource))

      this.separator = !!match[2];
    }

  }
  
  getName(){
    return "ChordGroup";
  }

  getChildren() {
    return this.chords;
  }
}