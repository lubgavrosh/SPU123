import React, { useEffect, useState } from "react";
import { Link } from "react-router-dom";
import ModalDelete from "../../../common/ModalDelete";
import http_common from "../../../../http_common";
import { ICategoryItem } from "./types";
import { APP_ENV } from "../../../../env";

const CategoryListPage = () => {
    const [list, setList] = useState<ICategoryItem[]>([]); // Створюємо стан для збереження списку категорій

    useEffect(() => {
        // Виконується при завантаженні компонента
        http_common
            .get<ICategoryItem[]>("api/category") // Виконуємо запит на сервер для отримання списку категорій
            .then((resp) => {
                console.log("Категорії", resp.data);
                setList(resp.data); // Зберігаємо список категорій в стані компонента
            });
    }, []);

    const onClickDelete = async (id: number) => {
        try {
            // Виконується при видаленні категорії
            await http_common.delete(`api/category/${id}`); // Виконуємо запит на сервер для видалення категорії
            setList(list.filter(x => x.id !== id)); // Видаляємо видалену категорію зі списку
        } catch {
            console.log("Помилка видалення");
        }
    };

    return (
        <>
            <div className="container">
                <h1 className="text-center">Список категорій</h1>
                <Link to="create" className="btn btn-success">Додати</Link>
                <table className="table">
                    <thead>
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">Назва</th>
                        <th scope="col">Фото</th>
                        <th scope="col">Опис</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>
                    {list.map((c) => {
                        return (
                            <tr key={c.id}>
                                <th scope="row">{c.id}</th>
                                <td>{c.name}</td>
                                <td>
                                    <img src={`${APP_ENV.BASE_URL}uploads/150_${c.image}`} alt="фото" width={50} />
                                </td>
                                <td>{c.description}</td>
                                <td>
                                    <ModalDelete id={c.id} text={c.name} deleteFunc={onClickDelete} />
                                    &nbsp; &nbsp;
                                    <Link to={`edit/${c.id}`} className="btn btn-info">Змінити</Link>
                                </td>
                            </tr>
                        );
                    })}
                    </tbody>
                </table>
            </div>
        </>
    );
}

export default CategoryListPage;
