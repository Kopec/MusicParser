import { SongPart } from "../schema";

export class TextPart implements SongPart {

  constructor(public source:string){
  }

  getChildren() {
    return [];
  }
}