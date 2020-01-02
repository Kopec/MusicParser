import { SongPart } from "../schema";
import { tone2index, index2tone } from "../util/transpose";

export class Diagram extends SongPart {

  re_diagram = /\[\[([^\]]+)\]\]/;
  re_tone = /^([A-H])(#|b)?/i;

  chord: string;
  code: string;

  constructor(source: string) {
    super(source);
    const matches = this.re_diagram.exec(source);

    if (matches) {
      [this.chord, this.code] = matches[1].split(" ");
    }
    else {
      this.chord = source;
      this.code = "";
    }
  }

  getName() {
    return `Diagram (Chord: ${this.chord}, Code: ${this.code})`;
  }

  getChildren() {
    return [];
  }

  getChordTone(): string | null {
    const matches = this.re_tone.exec(this.chord);

    return matches ? matches[1].toUpperCase() + (matches[2] || "").toLowerCase() : null;
  }

  getTones() {
    if (this.code.length < 6) {
      return undefined;
    }

    return this.code
      .match(this.code.length >= 6 && this.code.length < 12 ? /./g : /.{2}/g) // match pairs (12 char long) or single (6 chars long)
      ?.slice(0, 6) // get the first six strings (? means if not matched, return undefined)
      .map(string => /x{1,2}/i.test(string) ? null : Number(string)); // convert numbers to numbers and x to null

  }

  private tones2Code(tones: (number | null)[]): string {
    if (tones.some(tone => tone !== null ? tone >= 10 : false)) {
      return tones.map(tone => tone !== null ? String(tone).padStart(2, "0") : "xx").join("");
    }
    else {
      return tones.map(tone => tone !== null ? String(tone) : "x").join("");
    }
  }

  transpose(difference: number) {
    super.transpose(difference);

    this.transposeChord(difference);

    this.transposeTones(difference);
  }

  transposeChord(difference: number) {
    
    const oldTone = this.getChordTone();
    if (oldTone === null) return;

    const oldIndex = tone2index(oldTone);
    if (oldIndex === undefined) return;

    const newTone = index2tone(oldIndex + difference);

    this.chord = this.chord.replace(this.re_tone, newTone);
  }

  transposeTones(difference: number) {

    const tones = this.getTones();

    if (!tones) return false;

    var newTones = tones.map(tone => tone !== null ? tone + (difference % 12) : null);

    if (newTones.some(tone => tone !== null ? tone < 0 : false)) {
      newTones = newTones.map(tone => tone !== null ? tone + 12 : null);
    }
    else if (newTones.every(tone => tone ? tone >= 12 : false)) {
      newTones = newTones.map(tone => tone !== null ? tone - 12 : null);
    }

    this.code = this.tones2Code(newTones);
  }
}