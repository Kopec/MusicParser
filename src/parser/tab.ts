import { SongPart } from "../schema";

export class Tab implements SongPart {

  constructor(public source: string) { }
  
  getChildren() {
    return [];
  }

}