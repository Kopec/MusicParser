import { Exporter, SongPart } from "../schema";

export class StructureExporter implements Exporter {

  export(part: SongPart): any {
    const output: any = {
      name: (<any>part.constructor).name,
      source: part.source.substr(0, 20),
      children: []
    };
    try {
      output.children = part.getChildren().map(child => this.export(child))
    }
    catch (err) {
      console.log(part.constructor.name);
    }
    return output;
  }

}