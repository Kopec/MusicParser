import { Exporter } from "../schema";

import { createDiagram } from "./html/createDiagram";
import { Song, Section, Column, Verse, Tab, Text, TextPart, ChordGroup, Diagram, Chord } from "../structure";

export class HtmlExporter implements Exporter {

  export(song: Song): string {
    return `<div class="song">${song.getChildren().map(section => this.exportSection(section)).join("")}</div>`;
  }

  exportSection(section: Section): string {
    return `<div class="section">${section.getChildren().map(column => this.exportColumn(column)).join("")}</div>`;
  }

  exportColumn(column: Column) {
    return `<div class="column">${column.getChildren().map(verse => this.exportVerse(verse)).join("<br><br>")}</div>`;
  }

  exportVerse(verse: Verse) {
    const children: string = verse.getChildren()
      .map(child => {
        if (child instanceof Tab) return this.exportTab(child);
        else if (child instanceof Text) return this.exportText(child);
      })
      .join("");

    return `<div class="verse${verse.isChorus ? " chorus" : ""}"><div class="label">${verse.label}${verse.separator}</div>${children}`;
  }

  exportTab(tab: Tab): string {
    return `<div class="tab">${tab.source.replace(/\r?\n/g, "<br>")}</div>`;
  }

  exportText(text: Text): string {
    const children: string = text.getChildren()
      .map(child => {
        if (child instanceof TextPart) return this.exportTextPart(child);
        else if (child instanceof ChordGroup) return this.exportChordGroup(child);
        else if (child instanceof Diagram) return this.exportDiagram(child);
      })
      .join("");
    return `<div class="text">${children}</div>`;
  }

  exportTextPart(textpart: TextPart): string {
    return textpart.source
      .replace(/\r?\n/g, "<br>")
      .replace("...", "&hellip;");
  }

  exportChordGroup(chordgroup: ChordGroup): string {
    return `<span class="chordgroup">${chordgroup.getChildren().map(chord => this.exportChord(chord)).join("")}</span>`;
  }

  exportDiagram(diagram: Diagram): string {
    const tones = diagram.getTones();
    if (!tones) return diagram.source;
    return createDiagram(diagram.chord, tones);
  }

  exportChord(chord: Chord): any {
    return `<span class="chord">${chord.chord}</span>`;
  }

}