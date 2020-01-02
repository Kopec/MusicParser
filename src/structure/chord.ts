import { SongPart } from "../schema";
import { tone2index, index2tone } from "../util/transpose";
import { normalizeChord } from "../util/normalize-chord";

export class Chord extends SongPart {

  re_tone = /^[A-H][♯♭]?/i;

  chord: string;

  constructor(source: string) {
    super(source);

    this.chord = normalizeChord(source);

  }

  getTone() {
    const matches = this.chord.match(this.re_tone);

    return matches ? matches[0] : null;
  }

  setTone(newTone: string) {
    this.chord = this.chord.replace(this.re_tone, newTone);
  }

  transpose(difference: number) {

    super.transpose(difference);

    const oldTone = this.getTone();
    if (oldTone === null) return;

    const oldIndex = tone2index(oldTone);
    if (oldIndex === undefined) return;

    const newTone = index2tone(oldIndex + difference);

    this.setTone(newTone);
    
  }

  getName() {
    return `Chord (Chord: ${this.chord}, Tone: ${this.getTone()})`;
  }

  getChildren() {
    return [];
  }
}