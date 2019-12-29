import { Section } from "./section";
import { SongPart } from "../schema";

export class Song implements SongPart {

  re_section = /\n\-{3,}\n/;

  sections: Section[];

  constructor(public source: string) {
    this.sections = source.trim().split(this.re_section).map(sourcePart => new Section(sourcePart));
  }

  getChildren() {
    return this.sections;
  }

}