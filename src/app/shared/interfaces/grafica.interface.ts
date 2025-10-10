export interface Grafica {
    type : string;
    title : string;
    tags : string[];
    data : number[];
    tags_img : string[],
}

export interface DataItem{
    name: string,
    value: number
}

export interface UserGrafic{
    data: DataItem[],
    user: string,
    img: string,
}
