import { SongPart } from "../schema";
import { tone2index, index2tone } from "../util/transpose";

export class Chord extends SongPart {

  re_tone = /^([A-H])(#|b)?/i;

  chord: string;

  constructor(source: string) {
    super(source);

    this.chord = source;

  }

  getTone() {
    const matches = this.re_tone.exec(this.chord);

    return matches ? matches[1].toUpperCase() + (matches[2] || "").toLowerCase() : null;
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