import { SongPart } from "../schema";

export class Diagram implements SongPart {

  constructor(public source: string) { }

  getChildren() {
    return [];
  }
}