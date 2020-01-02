import { SongPart } from "../schema/songpart";
import { Tab } from "./tab";
import { Text } from "./text";

export class Verse extends SongPart {

  re_tab = /^[a-hA-H ]?\|/;
  re_chorus = /(^r$|^ref|^chorus)/i;

  children: (Text | Tab)[];

  isChorus: boolean;

  constructor(source: string, public label: string | null, public separator: string | null) {

    super(source);

    this.children = this.source
      .split(/\n\s*\n/)
      .map(blockSource => blockSource.trim())
      .filter(blockSource => blockSource.length > 0)
      .map(blockSource => {
        if (blockSource.match(this.re_tab)) return new Tab(blockSource);
        else return new Text(blockSource);
      })

    this.isChorus = label ? this.re_chorus.test(label) : false;
  }

  getName() {
    return "Verse";
  }

  getChildren() {
    return this.children;
  }
}