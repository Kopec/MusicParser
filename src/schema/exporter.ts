import { Song } from "../parser/song";

export interface Exporter {
  export(song: Song): any;
}