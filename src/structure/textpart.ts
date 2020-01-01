import { SongPart } from "../schema";

export class TextPart extends SongPart {

  constructor(source: string) {
    super(source)
  }

  getName() {
    return `TextPart (Source: ${this.source.substr(0, 20)})`;
  }

  getChildren() {
    return [];
  }
}