import { SongPart } from "./songpart";

export interface Exporter {
  export(song: SongPart): any;
}