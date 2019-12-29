import { SongPart } from "../schema";
import { Column } from "./column";

export class Section implements SongPart {

  re_column = /\n *\-{2,}> *\n/m;

  columns: Column[];

  constructor(public source: string) {
    this.columns = source.trim().split(this.re_column).map(sourcePart => new Column(sourcePart));
  }

  getChildren() {
    return this.columns;
  }

}