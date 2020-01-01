import { SongPart } from "../schema";

export class Tab extends SongPart {

  tab: string;

  constructor(source: string) {
    super(source);
    this.tab = source;
  }

  getName() {
    return `Tab (Source: ${this.source.substr(0, 20)})`;
  }

  getChildren() {
    return [];
  }

  transpose(difference: number) {
    super.transpose(difference);
    this.tab = this.tab.replace(/\d+/g, match => String(((((Number(match) + difference) % 12) + 12) % 12)));
  }

}