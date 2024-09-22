<template>
    <div class="container mx-auto p-4">
        <div
            class="overflow-x-auto shadow-lg sm:rounded-lg custom-table-container"
        >
            <table class="table-categories">
                <thead>
                    <tr>
                        <th>Catégorie</th>
                        <th>Sous-catégorie</th>
                        <th>Sous-sous-catégorie</th>
                        <th>Images</th>
                        <!-- Nouvelle colonne pour l'image -->
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="category in categories" :key="category.id">
                        <td>{{ category.name }}</td>
                        <td>
                            <ul>
                                <li
                                    v-for="child in category.children"
                                    :key="child.id"
                                >
                                    {{ child.name }}
                                </li>
                            </ul>
                        </td>
                        <td>
                            <ul>
                                <li
                                    v-for="child in category.children"
                                    :key="child.id"
                                >
                                    <ul v-if="child.children.length > 0">
                                        <li
                                            v-for="subChild in child.children"
                                            :key="subChild.id"
                                        >
                                            {{ subChild.name }}
                                        </li>
                                    </ul>
                                </li>
                            </ul>
                        </td>
                        <td>
                            <!-- Colonne pour l'image et le bouton d'insertion -->
                            <ul>
                                <li
                                    v-for="child in category.children"
                                    :key="child.id"
                                >
                                    <ul v-if="child.children.length > 0">
                                        <li
                                            v-for="subChild in child.children"
                                            :key="subChild.id"
                                        >
                                            <div
                                                class="image-button-container d-flex"
                                            >
                                                <!-- Afficher l'image si elle est présente -->
                                                <div v-if="subChild.image">
                                                    <img
                                                        :src="subChild.image"
                                                        alt="Image de la catégorie"
                                                        class="category-image mr-2"
                                                        style="
                                                            height: 30px;
                                                            width: 30px;
                                                        "
                                                    />
                                                </div>
                                                <!-- Afficher le bouton pour ajouter une image -->
                                                <button
                                                    @click="
                                                        uploadImage(subChild.id)
                                                    "
                                                    class="image-upload-btn"
                                                >
                                                    {{
                                                        subChild.image
                                                            ? "Changer"
                                                            : "Inserer"
                                                    }}
                                                </button>
                                                <input
                                                    type="file"
                                                    :id="
                                                        'file-input-' +
                                                        subChild.id
                                                    "
                                                    style="display: none"
                                                    @change="
                                                        handleFileUpload(
                                                            $event,
                                                            subChild.id
                                                        )
                                                    "
                                                />
                                            </div>
                                        </li>
                                    </ul>
                                </li>
                            </ul>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

<script>
export default {
    data() {
        return {
            categories: [],
        };
    },
    mounted() {
        this.fetchCategories();
    },
    methods: {
        fetchCategories() {
            axios
                .get("api/categories")
                .then((response) => {
                    this.categories = response.data;
                })
                .catch((error) => {
                    console.error("Error fetching categories:", error);
                });
        },
        uploadImage(categoryId) {
            // Ouvre la boîte de dialogue pour sélectionner une image
            const inputElement = document.getElementById(
                "file-input-" + categoryId
            );
            inputElement.click();
        },
        handleFileUpload(event, categoryId) {
            const file = event.target.files[0];
            if (file) {
                const formData = new FormData();
                formData.append("image", file);
                formData.append("category_id", categoryId);

                // Faire une requête API pour télécharger l'image
                axios
                    .post(
                        "/api/categories/" + categoryId + "/upload-image",
                        formData,
                        {
                            headers: {
                                "Content-Type": "multipart/form-data",
                            },
                        }
                    )
                    .then((response) => {
                        // Mettre à jour l'image localement pour la sous-sous-catégorie
                        const newImagePath = response.data.path; // Le chemin de l'image retourné par l'API

                        // Trouver la sous-sous-catégorie correspondante et mettre à jour son image
                        this.updateCategoryImage(categoryId, newImagePath);
                    })
                    .catch((error) => {
                        console.error(
                            "Erreur lors du téléchargement de l'image :",
                            error
                        );
                    });
            }
        },
        updateCategoryImage(categoryId, newImagePath) {
    // Parcourir les catégories pour trouver la sous-sous-catégorie à mettre à jour
    this.categories.forEach(category => {
      category.children.forEach(subCategory => {
        subCategory.children.forEach(subSubCategory => {
          if (subSubCategory.id === categoryId) {
            // Utiliser Vue.set pour que la mise à jour soit réactive
            this.$set(subSubCategory, 'image', newImagePath);
          }
        });
      });
    });
  }
    },
};
</script>
<style scoped>
.table-categories {
    width: 100%;
    border-collapse: collapse;
    margin: 20px 0;
    font-size: 16px;
    text-align: left;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.table-categories thead {
    background-color: #f9fafb;
    color: #6b7280;
    font-weight: 600;
}

.table-categories th,
.table-categories td {
    padding: 12px 20px;
}

.table-categories tbody tr {
    border-bottom: 1px solid #e5e7eb;
}

.table-categories tbody tr:hover {
    background-color: #f3f4f6;
}

.table-categories tbody tr:last-child {
    border-bottom: none;
}

.table-categories tbody td {
    color: #6b7280;
}

.table-categories tbody td:first-child {
    font-weight: 600;
}

.table-categories tbody td:nth-child(2) {
    color: #6b7280;
}

.table-categories tbody td:nth-child(3) {
    color: #6b7280;
}

.table-categories ul {
    list-style-type: none;
    padding-left: 0;
    margin: 0;
}

.table-categories ul li {
    padding: 5px 0;
}

.table-categories ul li ul {
    padding-left: 20px;
}
.image-upload-container {
  display: flex;
  align-items: center;
  gap: 10px; /* Espace entre l'image et le bouton */
}

.magasin-image {
  width: 50px;
  height: 50px;
  object-fit: cover;
  border-radius: 4px;
}

.image-upload-btn {
  padding: 5px 10px;
  background-color: #4caf50;
  color: white;
  border: none;
  border-radius: 4px;
  cursor: pointer;
}

.image-upload-btn:hover {
  background-color: #45a049;
}
</style>
