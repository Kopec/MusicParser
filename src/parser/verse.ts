import { SongPart } from "../schema/songpart";
import { Tab } from "./tab";
import { Text } from "./text";

export class Verse implements SongPart {

  re_tab = /^[a-hA-H ]?\|/;
  re_chorus = /(^r$|^ref|^chorus)/i;

  children: (Text | Tab)[];

  isChorus: boolean;

  constructor(public source: string, public label: string | null) {

    this.children = this.source
      .split(/\n\n/)
      .filter(blockSource => blockSource.trim().length)
      .map(blockSource => {
        if (blockSource.match(this.re_tab)) return new Tab(blockSource);
        else return new Text(blockSource);
      })

    this.isChorus = label ? this.re_chorus.test(label) : false;
  }

  getChildren() {
    return this.children;
  }
}