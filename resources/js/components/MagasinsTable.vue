<template>
  <v-container class="pa-4">
      <!-- Tableau des magasins avec Vuetify -->
      <v-card class="pa-4">
          <v-table density="compact">
              <thead>
                  <tr>
                      <th class="text-left">Nom du magasin</th>
                      <th class="text-left">Adresse</th>
                      <th class="text-left">Téléphone</th>
                      <th class="text-left">Secteur d'activité</th>
                      <th class="text-left">Tag</th>
                      <th class="text-left">Images</th>
                      <th class="text-left">Actions</th>
                  </tr>
              </thead>
              <tbody>
                  <tr v-for="magasin in magasins" :key="magasin.id">
                      <td>{{ magasin.nom }}</td>
                      <td>{{ magasin.adresse }}</td>
                      <td>{{ magasin.tel }}</td>
                      <td>{{ magasin.secteur }}</td>
                      <td>{{ magasin.tag }}</td>
                      <td>
                            <div class="image-upload-container">
                                <!-- Affichage de l'image s'il y en a une -->
                                <div v-if="magasin.image">
                                    <img :src="magasin.image" alt="Image du magasin" class="magasin-image" />
                                </div>
                                <!-- Bouton pour télécharger l'image -->
                                <button @click="uploadImage(magasin.id)" class="image-upload-btn">
                                    {{ magasin.image ? 'Changer' : 'Inserer' }} 
                                </button>
                                <input
                                    type="file"
                                    :id="'file-input-' + magasin.id"
                                    style="display:none"
                                    @change="handleFileUpload($event, magasin.id)"
                                />
                            </div>
                        </td>
                      <td>
                          <button @click="openAssignCategoryModal(magasin)" class="bouton  text-white px-2 py-1 rounded mr-2">
                              Associer
                          </button>
                          <button @click="openShowCategoriesModal(magasin)" class="bouton  text-white px-2 py-1 rounded">
                              Voir
                          </button>
                      </td>
                  </tr>
              </tbody>
          </v-table>
      </v-card>

      <transition name="modal-fade">
            <div v-if="showAssignCategoryModal" class="fixed inset-0 bg-gray-900 bg-opacity-50 flex justify-center items-center">
                <div class="bg-white p-6 rounded-lg shadow-xl w-80">
                    <!-- Menu déroulant pour la catégorie principale -->
                    <div class="relative mb-4">
                        <button @click="toggleDropdown('mainCategory')" class="border border-gray-300 p-2 w-full rounded custom-input text-left flex justify-between items-center">
                            {{ selectedMainCategoryName || "Catégorie" }}
                            <span><i class="fas fa-chevron-down icon-small"></i></span> <!-- Réduire la taille avec icon-small -->
                        </button>
                        <ul v-if="dropdownOpen === 'mainCategory'" class="absolute left-0 w-full bg-white border border-gray-300 rounded shadow-lg z-10 max-h-60 overflow-auto">
                            <li v-for="category in mainCategories" :key="category.id" @click="selectMainCategory(category)" class="px-4 py-2 cursor-pointer hover:bg-gray-100">
                                {{ category.name }}
                            </li>
                        </ul>
                    </div>

                    <!-- Menu déroulant pour la sous-catégorie -->
                    <div class="relative mb-4">
                        <button @click="toggleDropdown('subCategory')" class="border border-gray-300 p-2 w-full rounded custom-input text-left flex justify-between items-center">
                            {{ selectedSubCategoryName || "Sous catégorie" }}
                            <span><i class="fas fa-chevron-down icon-small"></i></span> <!-- Réduire la taille avec icon-small -->
                        </button>
                        <ul v-if="dropdownOpen === 'subCategory'" class="absolute left-0 w-full bg-white border border-gray-300 rounded shadow-lg z-10 max-h-60 overflow-auto">
                            <li @click="selectSubCategory({ id: 'all', name: 'Tout' })" class="px-4 py-2 cursor-pointer hover:bg-gray-100">Tout</li>
                            <li v-for="category in subCategories" :key="category.id" @click="selectSubCategory(category)" class="px-4 py-2 cursor-pointer hover:bg-gray-100">
                                {{ category.name }}
                            </li>
                        </ul>
                    </div>

                    <!-- Menu déroulant pour la sous-sous-catégorie -->
                    <div class="relative mb-4">
                        <button @click="toggleDropdown('subSubCategory')" class="border border-gray-300 p-2 w-full rounded custom-input text-left flex justify-between items-center">
                            {{ selectedSubSubCategoryName || "Sous sous catégorie" }}
                            <span><i class="fas fa-chevron-down icon-small"></i></span> <!-- Réduire la taille avec icon-small -->
                        </button>
                        <ul v-if="dropdownOpen === 'subSubCategory'" class="absolute left-0 w-full bg-white border border-gray-300 rounded shadow-lg z-10 max-h-60 overflow-auto">
                            <li @click="selectSubSubCategory({ id: 'all', name: 'Tout' })" class="px-4 py-2 cursor-pointer hover:bg-gray-100">Tout</li>
                            <li v-for="category in subSubCategories" :key="category.id" @click="selectSubSubCategory(category)" class="px-4 py-2 cursor-pointer hover:bg-gray-100">
                                {{ category.name }}
                            </li>
                        </ul>
                    </div>

                    <!-- Boutons d'action -->
                    <div class="flex justify-center mt-auto ">
                        <button @click="assignCategoryToMagasin" class="bouton  text-white px-2 py-1 rounded">
                            Affecter
                        </button>
                        <button @click="closeAssignCategoryModal" class="bouton  text-white px-2 py-1 rounded ml-2">
                            Annuler
                        </button>
                    </div>
                </div>
            </div>
        </transition>

      <!-- Modal pour Afficher et Supprimer les Catégories -->
<transition name="modal-fade">
  <div v-if="showShowCategoriesModal" class="fixed inset-0 bg-gray-900 bg-opacity-50 flex justify-center items-center">
    <div class="bg-white p-6 rounded-lg shadow-xl w-80 h-80 flex flex-col justify-center items-center">
      <h2 class="text-xl font-bold mb-4 text-center">Liste des catégories</h2>
      <ul class="mb-4 w-full">
        <li v-for="category in selectedMagasinCategories" :key="category.id" class="mb-2 text-center flex justify-between items-center">
          <span>{{ category.name }}</span>
          <button @click="removeCategoryFromMagasin(category)" class="text-red-500 hover:text-red-700 ml-2">
            ✖ <!-- Symbole de croix pour la suppression -->
          </button>
        </li>
      </ul>
      <div class="flex justify-end space-x-3 mt-auto">
        <button @click="closeShowCategoriesModal" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded">
          Fermer
        </button>
      </div>
    </div>
  </div>
</transition>

  </v-container>
</template>


<script>
import axios from "axios";

export default {
    data() {
        return {
            magasins: [],
            mainCategories: [],
            subCategories: [],
            subSubCategories: [],
            dropdownOpen: null, // Gestion de l'ouverture des dropdowns
            selectedMagasin: null,
            selectedMainCategoryId: '',
            selectedSubCategoryId: '',
            selectedSubSubCategoryId: '',
            selectedMainCategoryName: '',
            selectedSubCategoryName: '',
            selectedSubSubCategoryName: '',
            selectedMagasinCategories: [],
            showAssignCategoryModal: false,
            showShowCategoriesModal: false,
        };
    },
    mounted() {
        this.fetchMagasins();
        this.fetchMainCategories();
    },
    methods: {
        fetchMagasins() {
            axios.get("/api/magasins")
                .then(response => {
                    this.magasins = response.data;
                })
                .catch(error => {
                    console.error("Erreur lors de la récupération des magasins:", error);
                });
        },
        uploadImage(magasinId) {
      // Ouvre la boîte de dialogue pour sélectionner une image
      const inputElement = document.getElementById("file-input-" + magasinId);
      inputElement.click();
    },
    handleFileUpload(event, magasinId) {
      const file = event.target.files[0];
      if (file) {
        const formData = new FormData();
        formData.append("image", file);
        formData.append("magasin_id", magasinId);

        // Faire une requête API pour télécharger l'image
        axios
          .post("/api/magasins/" + magasinId + "/upload-image", formData, {
            headers: {
              "Content-Type": "multipart/form-data",
            },
          })
          .then((response) => {
           

            // Mettre à jour l'image localement pour le magasin
            this.updateMagasinImage(magasinId, newImagePath);
          })
          .catch((error) => {
            console.error("Erreur lors du téléchargement de l'image :", error);
          });
      }
    },
    updateMagasinImage(magasinId, newImagePath) {
      // Trouver le magasin correspondant et mettre à jour son image
      const magasin = this.magasins.find((magasin) => magasin.id === magasinId);
      if (magasin) {
        this.$set(magasin, "image", newImagePath);
      }
    },
        fetchMainCategories() {
            axios.get("/api/categories")
                .then(response => {
                    this.mainCategories = response.data;
                })
                .catch(error => {
                    console.error("Erreur lors de la récupération des catégories principales:", error);
                });
        },
        fetchSubCategories() {
            if (this.selectedMainCategoryId) {
                axios.get(`/api/categories/${this.selectedMainCategoryId}/subcategories`)
                    .then(response => {
                        this.subCategories = response.data;
                        this.subSubCategories = []; // Réinitialiser les sous-sous-catégories
                    })
                    .catch(error => {
                        console.error("Erreur lors de la récupération des sous-catégories:", error);
                    });
            }
        },
        fetchSubSubCategories() {
            if (this.selectedSubCategoryId) {
                axios.get(`/api/categories/${this.selectedSubCategoryId}/subsubcategories`)
                    .then(response => {
                        this.subSubCategories = response.data;
                    })
                    .catch(error => {
                        console.error("Erreur lors de la récupération des sous-sous-catégories:", error);
                    });
            }
        },
        toggleDropdown(dropdownType) {
            this.dropdownOpen = this.dropdownOpen === dropdownType ? null : dropdownType;
        },
        selectMainCategory(category) {
            this.selectedMainCategoryId = category.id;
            this.selectedMainCategoryName = category.name;
            this.dropdownOpen = null;
            this.fetchSubCategories();
        },
        selectSubCategory(category) {
            this.selectedSubCategoryId = category.id;
            this.selectedSubCategoryName = category.name;
            this.dropdownOpen = null;
            this.fetchSubSubCategories();
        },
        selectSubSubCategory(category) {
            this.selectedSubSubCategoryId = category.id;
            this.selectedSubSubCategoryName = category.name;
            this.dropdownOpen = null;
        },
        assignCategoryToMagasin() {
            const apiUrl = `/api/magasins/${this.selectedMagasin.id}/categories`;
            let categoryIds = [];

            if (this.selectedSubCategoryId === 'all') {
                categoryIds = this.subCategories.map(category => category.id);
            } else if (this.selectedSubSubCategoryId === 'all') {
                categoryIds = this.subSubCategories.map(category => category.id);
            } else {
                categoryIds = [this.selectedSubSubCategoryId];
            }

            axios.post(apiUrl, { category_ids: categoryIds })
                .then(response => {
                    console.log("Catégories affectées avec succès:", response.data);
                    this.closeAssignCategoryModal();
                })
                .catch(error => {
                    console.error("Erreur lors de l'affectation de la catégorie:", error);
                });
        },
        openAssignCategoryModal(magasin) {
            this.selectedMagasin = magasin;
            this.showAssignCategoryModal = true;
        },
        closeAssignCategoryModal() {
            this.showAssignCategoryModal = false;
            this.selectedMainCategoryId = '';
            this.selectedSubCategoryId = '';
            this.selectedSubSubCategoryId = '';
        },
        removeCategoryFromMagasin(category) {
    const apiUrl = `/api/magasins/${this.selectedMagasin.id}/categories/${category.id}`;
    
    // Envoyer une requête DELETE au serveur pour supprimer la catégorie
    axios.delete(apiUrl)
      .then(response => {
        console.log("Catégorie supprimée avec succès:", response.data);

        // Supprimer la catégorie de la liste locale
        this.selectedMagasinCategories = this.selectedMagasinCategories.filter(cat => cat.id !== category.id);
      })
      .catch(error => {
        console.error("Erreur lors de la suppression de la catégorie:", error);
        alert("Erreur lors de la suppression de la catégorie.");
      });
  },
        openShowCategoriesModal(magasin) {
            this.selectedMagasin = magasin;
            axios.get(`/api/magasins/${magasin.id}/categories`)
                .then(response => {
                    this.selectedMagasinCategories = response.data;
                    this.showShowCategoriesModal = true;
                })
                .catch(error => {
                    console.error("Erreur lors de la récupération des catégories affectées:", error);
                });
        },
        closeShowCategoriesModal() {
            this.showShowCategoriesModal = false;
            this.selectedMagasinCategories = [];
        },
    },
};
</script>


<style scoped>
.container {
    max-width: 1200px;
    margin: auto;
}

button {
    transition: background-color 0.3s ease;
}

.modal-fade-enter-active,
.modal-fade-leave-active {
    transition: opacity 0.3s ease;
}
.modal-fade-enter,
.modal-fade-leave-to {
    opacity: 0;
}

.bg-white {
    background-color: #ffffff;
    border: 1px solid #e5e7eb;
    box-shadow: 0 10px 15px rgba(0, 0, 0, 0.1), 0 4px 6px rgba(0, 0, 0, 0.05);
}

.w-80 {
    width: 20rem; /* Fixed width */
}

.h-80 {
    height: 20rem; /* Fixed height */
}
.bouton {
    background-color: #4c7dfb;
}
.icon-small {
    font-size: 12px; /* Ajustez la taille selon vos besoins */
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
