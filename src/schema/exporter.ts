import { Song } from "../structure";

export interface Exporter {
  export(song: Song): any;
}