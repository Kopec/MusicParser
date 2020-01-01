import { Exporter, SongPart } from "../schema";

import { createDiagram } from "./html/createDiagram";
import { Song, Section, Column, Verse, Tab, Text, TextPart, ChordGroup, Diagram, Chord } from "../structure";

export class HtmlExporter implements Exporter {

  export(part: SongPart): string {
    if(part instanceof Song) return this.exportSong(part);
    else if(part instanceof Section) return this.exportSection(part);
    else if(part instanceof Column) return this.exportColumn(part);
    else if(part instanceof Verse) return this.exportVerse(part);
    else if(part instanceof Tab) return this.exportTab(part);
    else if(part instanceof Text) return this.exportText(part);
    else if(part instanceof TextPart) return this.exportTextPart(part);
    else if(part instanceof ChordGroup) return this.exportChordGroup(part);
    else if(part instanceof Diagram) return this.exportDiagram(part);
    else if(part instanceof Chord) return this.exportChord(part);
    else return part.getName();
  }

  private exportSong(song: Song): string {
    return `<div class="song">${song.getChildren().map(section => this.exportSection(section)).join("")}</div>`;
  }

  private exportSection(section: Section): string {
    return `<div class="section">${section.getChildren().map(column => this.exportColumn(column)).join("")}</div>`;
  }

  private exportColumn(column: Column) {
    return `<div class="column">${column.getChildren().map(verse => this.exportVerse(verse)).join("<br><br>")}</div>`;
  }

  private exportVerse(verse: Verse) {
    const children: string = verse.getChildren()
      .map(child => {
        if (child instanceof Tab) return this.exportTab(child);
        else if (child instanceof Text) return this.exportText(child);
      })
      .join("");

    return `<div class="verse${verse.isChorus ? " chorus" : ""}"><div class="label">${verse.label}${verse.separator}</div>${children}`;
  }

  private exportTab(tab: Tab): string {
    return `<div class="tab">${tab.source.replace(/\r?\n/g, "<br>")}</div>`;
  }

  private exportText(text: Text): string {
    const children: string = text.getChildren()
      .map(child => {
        if (child instanceof TextPart) return this.exportTextPart(child);
        else if (child instanceof ChordGroup) return this.exportChordGroup(child);
        else if (child instanceof Diagram) return this.exportDiagram(child);
      })
      .join("");
    return `<div class="text">${children}</div>`;
  }

  private exportTextPart(textpart: TextPart): string {
    return textpart.source
      .replace(/\r?\n/g, "<br>")
      .replace("...", "&hellip;");
  }

  private exportChordGroup(chordgroup: ChordGroup): string {
    return `<span class="chordgroup${chordgroup.separator ? " separated" : ""}">${chordgroup.getChildren().map(chord => this.exportChord(chord)).join("")}</span>`;
  }

  private exportDiagram(diagram: Diagram): string {
    const tones = diagram.getTones();
    if (!tones) return diagram.source;
    return createDiagram(diagram.chord, tones);
  }

  private exportChord(chord: Chord): any {
    return `<span class="chord">${chord.chord}</span>`;
  }

}