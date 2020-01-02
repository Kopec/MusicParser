import { Exporter, SongPart } from "../schema";

export class DebugExporter implements Exporter {

  export(part: SongPart, spaces: number = 2): any {
    let output = `${part.getName()}`;
    let children = part.getChildren().map(child => `${" ".repeat(spaces)}- ${this.export(child, spaces + 2)}`).join("\n");
    return output + (children ? "\n" + children : "");
  }



}