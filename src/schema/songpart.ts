export interface SongPart {

  source: string

  getChildren(): SongPart[];

}