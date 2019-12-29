import { SongPart } from "../schema";

export class Chord implements SongPart {

  constructor(public source: string) { }

  getChildren() {
    return [];
  }
}