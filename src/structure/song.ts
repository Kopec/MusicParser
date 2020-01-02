import { Section } from "./section";
import { SongPart } from "../schema";
import { Chord } from "./chord";
import { tone2index, index2tone } from "../util/transpose";

export class Song extends SongPart {

  re_section = /\n\-{3,}\n/;

  sections: Section[];

  constructor(source: string) {
    super(source);
    this.sections = source.trim().split(this.re_section).map(sourcePart => new Section(sourcePart));
  }

  getName() {
    return "Song";
  }

  getTune(): string | null {
    const chords = <Chord[]>this.getAllChildren().filter(child => child instanceof Chord);
    return chords[0] ? chords[0].getTone() : null;
  }

  setTune(newTune: string): boolean {
    
    const currentTune = this.getTune();
    if (!currentTune) return false;

    const currentIndex = tone2index(currentTune);
    if (currentIndex === undefined) return false; // tone index was not found

    let difference: number;
    if (typeof newTune === "number") difference = newTune;
    else {
      const newIndex = tone2index(newTune);
      if (newIndex === undefined) return false;
      difference = newIndex - currentIndex;
    }

    this.transpose(difference);

    return true;
  }

  getChildren() {
    return this.sections;
  }

}