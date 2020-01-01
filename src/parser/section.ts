import { SongPart } from "../schema";
import { Column } from "./column";

export class Section extends SongPart {

  re_column = /\n *\-{2,}> *\n/m;

  columns: Column[];

  constructor(source: string) {
    super(source);
    this.columns = source.trim().split(this.re_column).map(sourcePart => new Column(sourcePart));
  }

  getName(){
    return "Section";
  }

  getChildren() {
    return this.columns;
  }

}