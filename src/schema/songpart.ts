export abstract class SongPart {


  constructor(public source: string) {
  }

  abstract getChildren(): SongPart[];

  getAllChildren(): SongPart[] {
    return [
      ...this.getChildren(),
      ...this.getChildren().map(child => child.getAllChildren()).reduce((acc, cur) => [...acc, ...cur], [])
    ];
  };

  transpose(difference: number): void {
    return this.getChildren().forEach(child => child.transpose(difference));
  }

  abstract getName(): string;

}