<template>
    <div v-if="!searchStarted" class="header">
        <h1>La marketplace du commerce Lyonnais</h1>
        <p>
            Cherchez parmi de:<span class="highlight">
                milliers de magasins</span
            >
        </p>
    </div>
    <div class="container mx-auto pt-2 flex flex-col items-center">
        <div class="NavBar flex justify-center space-x-8 mb-4 w-full max-w-4xl">
            <!-- Menu déroulant pour les catégories -->
            <div
                class=" relative"
                style="font-family: Georgia, serif; width: 15%"
            >
                <div class="categorie relative">
                    <button
                        ref="dropdownButton"
                        @click="toggleDropdown"
                        class="border border-gray-300 p-2 text-gray-600 w-full rounded custom-input text-left flex items-center"
                    >
                        <span class="mr-2">
                            <i class="fas fa-bars icon-small text-gray-400" ></i>
                            <!-- Icône de barre à gauche -->
                        </span>
                        {{ selectedDropdownCategoryName || "Catégorie" }}
                        <span
                            class="ml-auto transition-transform duration-300"
                            :class="{
                                'rotate-180': dropdownOpen, // Applique la rotation lorsque le menu est ouvert
                                'rotate-0': !dropdownOpen, // Retour à la position initiale lorsque le menu est fermé
                            }"
                        >
                            <i class="fas fa-chevron-down icon-small"></i>
                            <!-- Icône de flèche vers le bas à droite -->
                        </span>
                    </button>

                    <!-- Menu déroulant -->
                    <ul
                        v-if="dropdownOpen"
                        ref="dropdown"
                        class="absolute left-0 w-full bg-white border border-gray-300 rounded shadow-lg z-10 max-h-60 overflow-auto"
                    >
                        <li
                            v-for="(category, index) in categories"
                            :key="category.id"
                            @click="selectDropdownCategory(category)"
                            :class="{
                                'border-b border-gray-200':
                                    index < categories.length - 1,
                            }"
                            class="px-4 py-2 cursor-pointer hover:bg-gray-100"
                        >
                            {{ category.name }}
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Recherche par catégorie avec icône -->
            <div
                class="relative ml-2"
                style="font-family: Georgia, serif; width: 35%"
            >
                <div class="recherche-bar relative">
                    <span
                        class="absolute inset-y-0 left-3 flex items-center text-gray-200"
                    >
                        <i class="fas fa-search icon-small"></i>
                    </span>

                    <input
                        type="text"
                        v-model="searchQuery"
                        @input="debouncedFilterCategories"
                        @focus="showDropdown = true"
                        @blur="hideDropdown"
                        @keydown.enter.prevent="searchWithKeyword"
                        id="search"
                        class="border border-gray-300 p-2 w-full pl-10 pr-10 rounded custom-input"
                        placeholder="Rechercher dans des magasins à proximité"
                    />

                    <!-- Bouton "X" pour effacer le texte, visible uniquement si searchQuery n'est pas vide -->
                    <span
                        v-if="searchQuery"
                        class="absolute flex items-center cursor-pointer text-gray-500"
                        @click="clearSearch"
                    >
                        <i class="fas fa-times"></i>
                        <!-- Icône "X" -->
                    </span>
                </div>

                <!-- Dropdown des résultats de la recherche -->
                <ul
                    v-if="showDropdown && filteredCategories.length > 0"
                    class="recherche-bar absolute left-0 w-full bg-white border border-gray-300 rounded shadow-lg z-10 max-h-60 overflow-auto"
                >
                    <li
                        v-for="(category, index) in filteredCategories"
                        :key="category.id"
                        @mousedown="selectSearchCategory(category)"
                        :class="{
                            'border-b border-gray-200':
                                index < filteredCategories.length - 1,
                        }"
                        class="flex items-center px-4 py-2 cursor-pointer hover:bg-gray-100"
                    >
                        <!-- Icon at the beginning -->
                        <span
                            class="flex-shrink-0 bg-gray-200 rounded-full p-2 mr-3"
                        >
                            <i class="fas fa-search text-gray-500"></i>
                        </span>

                        <!-- Text with dynamic category hierarchy -->
                        <span>
                            <!-- Check if there is a subcategory or sub-subcategory -->
                            <span
                                v-if="category.name.split(' dans ').length > 2"
                                class="text-orange-600 ml-1 mr-1"
                            >
                                {{ category.name.split(" dans ")[2] }}
                            </span>
                            <span
                                v-if="category.name.split(' dans ').length > 2"
                                class="text-gray-500 ml-1 mr-1"
                                >dans</span
                            >
                            <span
                                v-if="category.name.split(' dans ').length > 1"
                                class="text-orange-800 font-semibold"
                            >
                                {{ category.name.split(" dans ")[1] }}
                            </span>
                            <span
                                v-if="category.name.split(' dans ').length > 1"
                                class="text-gray-500 ml-1 mr-1"
                                >dans</span
                            >
                            <span
                                v-if="category.name.split(' dans ').length > 0"
                                class="text-orange-600 font-semibold"
                            >
                                {{ category.name.split(" dans ")[0] }}
                            </span>
                        </span>
                    </li>
                </ul>
            </div>

            <!-- Barre de localisation -->
            <div
                class="rayon relative ml-2"
                style="font-family: Georgia, serif; width: 25"
            >
                <div class="relative">
                    <i
                        class="fas fa-map-marker-alt text-gray-400 absolute left-3 top-1/2 transform -translate-y-1/2"
                    ></i>
                    <input
                        id="location-input"
                        ref="locationInput"
                        type="text"
                        class="border border-gray-300 p-2 pl-10 w-full rounded custom-input text-gray-600"
                        placeholder="Localisation"
                    />
                </div>
            </div>
            <div
                class="rayon relative ml-2"
                style="font-family: Georgia, serif; width: 12%"
            >
                <div class="relative">
                    <button
                        ref="radiusButton"
                        @click="toggleRadiusDropdown"
                        class="border border-gray-300 p-2 w-full rounded custom-input text-left text-gray-600 flex items-center"
                    >
                        {{ selectedRadiusName || "Rayon" }}
                        <span
                            class="ml-auto transition-transform duration-300"
                            :class="
                                radiusDropdownOpen ? 'rotate-180' : 'rotate-0'
                            "
                        >
                            <i
                                class="fas fa-chevron-down icon-small text-gray-400"
                            ></i>
                            <!-- Icône de flèche vers le bas à droite -->
                        </span>
                    </button>
                    <!-- Dropdown pour la sélection de rayon -->
                    <ul
                        v-if="radiusDropdownOpen"
                        ref="radiusDropdown"
                        class="absolute left-0 w-full bg-white border border-gray-300 rounded shadow-lg z-10 max-h-60 overflow-auto"
                    >
                        <li
                            v-for="(radius, index) in radiusOptions"
                            :key="index"
                            @click="selectRadius(radius)"
                            :class="{
                                'border-b border-gray-200':
                                    index < radiusOptions.length - 1,
                            }"
                            class="px-4 py-2 text-gray-600 cursor-pointer hover:bg-gray-100 flex items-center"
                        >
                            <!-- Icône de rayon (ex: bullseye ou circle-notch) -->
                            <i class="fas fa-bullseye text-gray-400 mr-2"></i>
                            {{ radius.name }}
                        </li>
                    </ul>
                </div>
            </div>
        </div>
     
        <!-- Conteneur pour afficher le résumé de la recherche -->
        <div
            v-if="searchCompleted && magasins.length > 0"
            class="w-full max-w-4xl bg-white border border-gray-200 rounded-lg shadow p-4 mb-2"
            style="font-family: Georgia, serif"
        >
            <!-- En-tête avec titre et image -->
            <div class="flex items-center justify-between">
                <!-- Texte à gauche -->
                <div class="flex-1">
                    <div class="flex items-center mb-1">
                        <i class="fas fa-search text-cyan-500 mr-2" style="color: #4C7DFB;"></i>
                        <p class="text-lg font-semibold text-gray-900">
                            {{ selectedDropdownCategoryName || searchQuery }}
                            <!-- Afficher la catégorie sélectionnée ou le mot-clé -->
                            <span class="text-md text-gray-500">à</span>
                            <span class="ml-2 capitalize">{{
                                selectedLocation
                            }}</span>
                        </p>
                    </div>

                    <!-- Affichage du nombre de magasins trouvés -->
                    <div class="flex items-center">
                        <i class="fas fa-store text-cyan-500 mr-1" style="color: #4C7DFB;"></i>
                        <p class="text-sm text-gray-600">
                            {{ magasins.length
                            }}<span class="ml-1 font-medium text-gray-800">
                                Résultats à proximité</span
                            >
                        </p>
                    </div>
                </div>

                <!-- Image à droite -->
                <img
                    v-if="
                        findSelectedSubSubCategory() ||
                        findSubSubCategoryByKeyword() ||
                        findSelectedMainCategory()
                    "
                    :src="
                        (
                            findSelectedSubSubCategory() ||
                            findSubSubCategoryByKeyword() ||
                            findSelectedMainCategory()
                        ).image
                    "
                    alt="Image de la sous-sous-catégorie"
                    class="ml-2 rounded shadow-lg"
                    style="height: 120px; width: auto"
                />
            </div>
        </div>
        <div v-if="searchCompleted && magasins.length > 0" class="mb-2 " >
            <ToggleSwitch
                :currentView="viewMode"
                @switch-view="handleViewSwitch"
            />
        </div>
        <!-- Conteneur pour les cartes des magasins et la carte Google Maps -->
        <div class="flex-grow flex justify-between w-full h-full max-w-4xl">
            <div v-if="isLoading" class="loader-container">
                <!-- Remplacement du loader CSS par une image GIF -->
                <img
                    src="/public/images/radar.gif"
                    alt="Loading..."
                    class="radar-gif"
                />
            </div>

            <!-- Store cards (scrollable) -->
            <div
                class="overflow-y-auto w-full max-w-4xl mb-4"
                :class="[
                    'store-cards-container',
                    viewMode === 'list' ? 'w-full' : 'w-demi',
                ]"
            >
                <div v-if="magasins.length > 0" class="grid grid-cols-1 gap-4">
                    <div
                        v-for="magasin in magasins"
                        :key="magasin.id"
                        class="bg-white border border-gray-200 rounded-lg shadow p-4 flex justify-between items-start"
                    >
                        <!-- Détails du magasin -->
                        <div class="w-3/5 pr-4">
                            <h3 class="text-lg font-semibold mb-2">
                                {{ magasin.nom }}
                            </h3>
                            <p class="text-xs text-gray-600 flex items-center">
                                <i
                                    class="fas fa-map-marker-alt text-gray-400 mr-2" style="color: #4C7DFB;"
                                ></i>
                                {{ magasin.adresse }}
                            </p>
                            <p class="text-xs text-gray-500 mt-2">
                                <i class="fas fa-road mr-1" style="color: #4C7DFB;"></i> {{ magasin.distance.toFixed(2) }} km
                            </p>
                            <!-- Boutons -->
                            <div class="button-container flex space-x-4 mt-4">
                                <button
                                    @click="
                                        magasin.showPhone = !magasin.showPhone
                                    "
                                    class="flex items-center px-4 py-2 bg-cyan-400 rounded-full text-xs shadow"
                                >
                                    <i class="fas fa-phone-alt mr-2" style="color: #4C7DFB;"></i>
                                    {{
                                        magasin.showPhone
                                            ? magasin.tel
                                            : "Afficher le n°"
                                    }}
                                </button>
                                <a
                                    :href="`http://maps.google.com/?q=${magasin.adresse}`"
                                    target="_blank"
                                    class="flex items-center px-4 py-2 border border-cyan-400 text-cyan-400 rounded-full text-xs" style="width: 122px;"
                                >
                                    <i class="fas fa-location-arrow mr-2" style="color: #4C7DFB; "> </i> Y
                                    aller
                                </a>
                            </div>
                        </div>
                        <!-- Image du magasin -->
                        <div class="image-container w-2/5 flex justify-end">
                            <img
                                v-if="magasin.image"
                                :src="magasin.image"
                                alt="Image du magasin"
                                class="rounded-lg magasin-image"
                                style="width: auto; height: 130px"
                            />
                            <div
                                v-else
                                class="w-48 h-48 bg-gray-200 flex items-center justify-center rounded-lg"
                            >
                                <span class="text-gray-500">Pas d'image</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div v-else-if="searchAttempted && !isLoading">
                    <p>Aucun magasin trouvé!</p>
                </div>
            </div>

            <!-- Affichage de la carte Google Maps uniquement si la vue "graph" est active -->
            <div v-if="viewMode === 'graph'" class=" h-full">
                <div id="map" class="map-container w-full h-full"></div>
            </div>
        </div>
        
    </div>
   
</template>

<script>
import axios from "axios";
import _ from "lodash";
import ToggleSwitch from "./ToggleSwitch.vue";

export default {
    data() {
        return {
            selectedDropdownCategory: null,
            dropdownOpen: false,
            selectedDropdownCategoryName: "",

            searchQuery: "",
            allCategories: [],
            filteredCategories: [],
            selectedSearchCategory: null,
            selectedSearchCategoryName: "",
            selectedSearchCategoryId: "",
            selectedLocation: null,
            magasins: [],
            showDropdown: false,
            searchAttempted: false,
            isLoading: false,
            map: null,
            markers: [],
            userLat: null,
            userLng: null,
            searchCompleted: false,
            searchTriggered: false,
            dropdownSelection: false,
            searchStarted: false,
            viewMode: "graph",

            radiusDropdownOpen: false,
            selectedRadius: 5, // Valeur par défaut pour le rayon sélectionné
            selectedRadiusName: "5 km", // Nom affiché pour le rayon sélectionné
            showPhone: false,
            radiusOptions: [
                
                
                { name: "10 km", value: 10 },
                { name: "15 km", value: 15 },
                { name: "20 km", value: 20 },
                { name: "25 km", value: 25 },
            ],
        };
    },
    components: {
        ToggleSwitch, // Enregistrement du SwitchButton
    },
    mounted() {
        this.fetchAllCategories();
        this.initAutocomplete(); // Initialise Google Places Autocomplete
        this.getUserLocation(); // Récupère la localisation de l'utilisateur
        // Ajouter l'écouteur pour détecter les clics externes
        document.addEventListener("click", this.handleClickOutside);
        document.addEventListener(
            "click",
            this.handleClickOutsideRadiusDropdown
        );
    },
    created() {
        this.debouncedFilterCategories = _.debounce(this.filterCategories, 10);
    },
    beforeDestroy() {
        // Supprimer l'écouteur pour éviter les fuites de mémoire
        document.removeEventListener("click", this.handleClickOutside);
    },
    beforeDestroy() {
        document.removeEventListener(
            "click",
            this.handleClickOutsideRadiusDropdown
        );
    },
    methods: {
        delay(ms) {
            return new Promise((resolve) => setTimeout(resolve, ms));
        },
        handleViewSwitch(view) {
            this.viewMode = view;

            if (view === "graph") {
                this.$nextTick(() => {
                    this.initMap(); // Réinitialiser la carte lorsque l'utilisateur bascule vers 'Graph'
                });
            }
        },
        findSelectedMainCategory() {
            // Assurez-vous que vous avez une catégorie sélectionnée
            const selectedCategory =
                this.selectedSearchCategory || this.selectedDropdownCategory;
            if (!selectedCategory) return null;

            // Parcourez les catégories principales pour trouver celle correspondant à la sélection
            for (let category of this.categories) {
                // Si l'ID de la catégorie principale correspond à celui sélectionné, renvoyez cette catégorie
                if (category.id === selectedCategory.id) {
                    return category;
                }

                // Si la sélection correspond à une sous-catégorie ou sous-sous-catégorie, retournez la catégorie principale correspondante
                for (let child of category.children) {
                    if (child.id === selectedCategory.id) {
                        return category; // Retourner la catégorie principale
                    }

                    for (let subChild of child.children) {
                        if (subChild.id === selectedCategory.id) {
                            return category; // Retourner la catégorie principale
                        }
                    }
                }
            }

            // Retournez null si aucune catégorie principale n'est trouvée
            return null;
        },
        findSelectedSubSubCategory() {
            // Assurez-vous que vous avez une catégorie sélectionnée
            const selectedCategory =
                this.selectedSearchCategory || this.selectedDropdownCategory;
            if (!selectedCategory) return null;

            // Parcourez les catégories, sous-catégories, et sous-sous-catégories pour trouver la sélection
            for (let category of this.categories) {
                for (let child of category.children) {
                    for (let subChild of child.children) {
                        // Si le nom ou l'ID correspond à celui sélectionné, retournez la sous-sous-catégorie
                        if (subChild.id === selectedCategory.id) {
                            return subChild;
                        }
                    }
                }
            }

            // Retournez null si aucune sous-sous-catégorie n'est trouvée
            return null;
        },
        findSubSubCategoryByKeyword() {
            if (!this.searchQuery) return null;

            for (let category of this.categories) {
                for (let child of category.children) {
                    for (let subChild of child.children) {
                        // Si le nom de la sous-sous-catégorie correspond au mot-clé, on la retourne
                        if (
                            subChild.name
                                .toLowerCase()
                                .includes(this.searchQuery.toLowerCase())
                        ) {
                            return subChild;
                        }
                    }
                }
            }
            return null;
        },
        handleClickOutside(event) {
            // Vérifiez si le clic est à l'intérieur du menu déroulant ou du bouton
            const dropdown = this.$refs.dropdown;
            const button = this.$refs.dropdownButton;

            // Si l'élément cliqué n'est pas à l'intérieur du menu ou du bouton, fermer le menu
            if (
                dropdown &&
                button &&
                !dropdown.contains(event.target) &&
                !button.contains(event.target)
            ) {
                this.dropdownOpen = false;
            }
        },
        handleClickOutsideRadiusDropdown(event) {
            // Cibler le bouton et le menu déroulant de rayon
            const radiusButton = this.$refs.radiusButton;
            const radiusDropdown = this.$refs.radiusDropdown;

            // Si le clic ne se fait pas sur le bouton ou le menu, fermer le menu
            if (
                radiusDropdown &&
                radiusButton &&
                !radiusDropdown.contains(event.target) &&
                !radiusButton.contains(event.target)
            ) {
                this.radiusDropdownOpen = false;
            }
        },
        initMap() {
            // Initialiser la carte
            this.map = new google.maps.Map(document.getElementById("map"), {
                center: { lat: 48.8566, lng: 2.3522 }, // Position de départ (Paris)
                zoom: 11,
                disableDefaultUI: true,
                mapTypeId: "roadmap",
            });

            // Vérifier si la localisation de l'utilisateur est disponible
            if (this.userLat && this.userLng) {
                // Marquer la position actuelle de l'utilisateur
                const userPosition = { lat: this.userLat, lng: this.userLng };
                const userMarker = new google.maps.Marker({
                    position: userPosition,
                    map: this.map,
                    icon: {
                        path: google.maps.SymbolPath.CIRCLE, // Utilisation d'une forme de cercle
                        scale: 3, // Taille du cercle
                        fillColor: "#007bff", // Couleur de remplissage (bleu)
                        fillOpacity: 4, // Opacité de remplissage
                        strokeWeight: 3, // Bordure du cercle
                        strokeColor: "#007bff", // Couleur de la bordure (blanc)
                    },
                    title: "Vous êtes ici",
                });

                // Centrer la carte sur la position actuelle
                this.map.setCenter(userPosition);

                // Dessiner un cercle représentant le rayon sélectionné
                const userRadiusCircle = new google.maps.Circle({
                    strokeColor: "#00ffff", // Couleur du contour
                    strokeOpacity: 0.1,
                    strokeWeight: 2,
                    fillColor: "#00ffff", // Couleur de remplissage
                    fillOpacity: 0.2,
                    map: this.map,
                    center: userPosition,
                    radius: this.selectedRadius * 1000, // Rayon en mètres
                });
            }

            // Mettre à jour les marqueurs des magasins

            this.updateMapMarkers();
        },
        clearSearch() {
            // Méthode pour effacer le contenu du champ de recherche
            this.searchQuery = "";
            this.showDropdown = false;
        },
        updateMapMarkers() {
            if (!this.map) {
                this.initMap(); // S'assurer que la carte est initialisée
            }

            // Effacer les anciens marqueurs
            this.markers.forEach((marker) => marker.setMap(null));
            this.markers = [];

            const geocoder = new google.maps.Geocoder();

            // Filtrer les magasins selon le rayon sélectionné après le géocodage
            const magasinsFiltrés = [];

            // Processus asynchrone de géocodage
            const geocodingPromises = this.magasins.map((magasin) => {
                return new Promise((resolve) => {
                    geocoder.geocode(
                        { address: magasin.adresse },
                        (results, status) => {
                            if (status === "OK" && results[0]) {
                                const position = results[0].geometry.location;

                                // Calculer la distance entre la position de l'utilisateur et le magasin
                                const distance = this.calculateDistance(
                                    this.userLat,
                                    this.userLng,
                                    position.lat(),
                                    position.lng()
                                );

                                // Si le magasin est dans le rayon sélectionné, on l'ajoute aux magasins filtrés
                                if (distance <= this.selectedRadius) {
                                    magasin.distance = distance;

                                    // Assignation des coordonnées lat/lng au magasin
                                    magasin.lat = position.lat();
                                    magasin.lng = position.lng();

                                    magasinsFiltrés.push(magasin);

                                    // Créer le marqueur pour chaque magasin
                                    const marker = new google.maps.Marker({
                                        map: this.map,
                                        position: position,
                                        title: magasin.nom,
                                    });

                                    // Ajouter le marqueur à la carte et à la liste des marqueurs
                                    this.markers.push(marker);
                                }

                                resolve();
                            } else {
                                console.error(
                                    `Erreur de géocodage pour ${magasin.adresse}: ${status}`
                                );
                                resolve(); // Résoudre même en cas d'erreur pour continuer le géocodage des autres magasins
                            }
                        }
                    );
                });
            });

            // Une fois que toutes les adresses ont été géocodées et que les marqueurs sont placés
            Promise.all(geocodingPromises).then(() => {
                // Si au moins un magasin est trouvé, centrer la carte sur le premier magasin trouvé
                if (magasinsFiltrés.length > 0) {
                    const firstMagasin = magasinsFiltrés[0];

                    // Vérifier que les coordonnées sont valides avant de centrer la carte
                    if (
                        isFinite(firstMagasin.lat) &&
                        isFinite(firstMagasin.lng)
                    ) {
                        const firstPosition = new google.maps.LatLng(
                            firstMagasin.lat,
                            firstMagasin.lng
                        );
                        this.map.setCenter(firstPosition);
                    } else {
                        console.error("Coordonnées invalides:", firstMagasin);
                    }
                } else {
                    // Si aucun magasin n'est trouvé dans le rayon, centrer la carte sur la position de l'utilisateur
                    if (isFinite(this.userLat) && isFinite(this.userLng)) {
                        this.map.setCenter({
                            lat: this.userLat,
                            lng: this.userLng,
                        });
                    } else {
                        console.error(
                            "Coordonnées utilisateur invalides:",
                            this.userLat,
                            this.userLng
                        );
                    }
                }
            });
        },
        fetchAllCategories() {
            axios
                .get("/api/categories")
                .then((response) => {
                    this.allCategories = this.flattenCategories(response.data);
                    this.categories = response.data;
                })
                .catch((error) => {
                    console.error(
                        "Erreur lors de la récupération des catégories:",
                        error
                    );
                });
        },
        flattenCategories(categories) {
            let flattened = [];
            categories.forEach((category) => {
                if (category.children) {
                    category.children.forEach((subcategory) => {
                        if (subcategory.children) {
                            subcategory.children.forEach((subsubcategory) => {
                                // Ajouter une ligne pour la catégorie, sous-catégorie et sous-sous-catégorie
                                flattened.push({
                                    name: `${category.name} dans ${subcategory.name} dans ${subsubcategory.name}`,
                                    id: subsubcategory.id,
                                });
                            });
                        } else {
                            // Ajouter une ligne pour la catégorie et la sous-catégorie
                            flattened.push({
                                name: `${category.name} dans ${subcategory.name}`,
                                id: subcategory.id,
                            });
                        }
                    });
                } else {
                    // Ajouter une ligne juste pour la catégorie
                    flattened.push({
                        name: `${category.name}`,
                        id: category.id,
                    });
                }
            });
            return flattened;
        },

        getUserLocation() {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    (position) => {
                        this.userLat = position.coords.latitude;
                        this.userLng = position.coords.longitude;
                        this.reverseGeocode(this.userLat, this.userLng); // Appelle la méthode de géocodage inversé
                    },
                    (error) => {
                        console.error("Erreur de géolocalisation:", error);
                        alert("Impossible de récupérer votre position.");
                        this.setDefaultLocation(); // Utiliser la localisation par défaut si la géolocalisation échoue
                    }
                );
            } else {
                this.setDefaultLocation();
            }
        },

        // Nouvelle méthode pour convertir les coordonnées en adresse
        reverseGeocode(lat, lng) {
            const geocoder = new google.maps.Geocoder();
            const latlng = { lat: parseFloat(lat), lng: parseFloat(lng) };

            geocoder.geocode({ location: latlng }, (results, status) => {
                if (status === "OK") {
                    if (results[0]) {
                        const address = results[0].formatted_address;
                        this.selectedLocation = address; // Mettre à jour la variable qui lie l'input de localisation
                        this.$refs.locationInput.value = address; // Mettre à jour l'input visuellement
                    } else {
                        console.error("Aucune adresse trouvée");
                        this.setDefaultLocation(); // Utiliser la localisation par défaut si aucune adresse n'est trouvée
                    }
                } else {
                    console.error("Erreur lors du géocodage: " + status);
                    this.setDefaultLocation();
                }
            });
        },

        setDefaultLocation() {
            this.selectedLocation = "Lyon, France"; // Localisation par défaut si la géolocalisation échoue
            this.$refs.locationInput.value = "Lyon, France";
            this.fetchMagasins();
        },
        initAutocomplete() {
            const input = this.$refs.locationInput;
            const autocomplete = new google.maps.places.Autocomplete(input, {
                types: ["geocode"], // Limiter aux villes
            });

            autocomplete.addListener("place_changed", () => {
                const place = autocomplete.getPlace();
                if (place.geometry) {
                    // Mettre à jour la latitude et la longitude de la localisation choisie (Paris)
                    this.userLat = place.geometry.location.lat();
                    this.userLng = place.geometry.location.lng();
                    this.selectedLocation = place.formatted_address; // Met à jour l'adresse sélectionnée

                    // Rechercher les magasins en fonction de la nouvelle localisation
                    this.fetchMagasins();
                }
            });
        },
        filterCategories() {
            const query = this.searchQuery.toLowerCase();
            this.filteredCategories = this.allCategories.filter((category) =>
                category.name.toLowerCase().includes(query)
            );
        },
        selectDropdownCategory(category) {
            this.selectedDropdownCategory = category;
            this.selectedDropdownCategoryName = category.name;
            this.selectedDropdownCategoryId = category.id;
            this.dropdownOpen = false;
            this.dropdownSelection = true;
            this.searchTriggered = true;

            // Réinitialiser toutes les valeurs liées à la Recherche par catégorie
            this.selectedSearchCategory = null;
            this.selectedSearchCategoryName = "";
            this.searchQuery = ""; // Réinitialiser le champ de recherche
            this.selectedSearchCategoryId = "";

            // Appeler fetchMagasinsByCategory pour filtrer par catégorie du menu déroulant
            this.fetchMagasinsByCategory(this.selectedDropdownCategoryId);
        },
        selectSearchCategory(category) {
            this.selectedSearchCategory = category;
            this.selectedSearchCategoryName = category.name;
            this.searchQuery = category.name;
            this.selectedSearchCategoryId = category.id;
            this.showDropdown = false;

            // Réinitialiser le menu déroulant pour les catégories
            this.selectedDropdownCategory = null;
            this.selectedDropdownCategoryName = "";
            this.selectedDropdownCategoryId = "";
            this.dropdownSelection = false;

            // Re-fetch magasins with the new search category and existing location
            if (this.selectedLocation) {
                this.fetchMagasins();
            }
        },

        searchWithKeyword() {
            // Utiliser la requête de recherche libre, même si aucune catégorie n'est sélectionnée
            if (this.searchQuery.trim() !== "") {
                this.selectedSearchCategory = null; // Réinitialiser la catégorie sélectionnée
                this.selectedDropdownCategoryName = "";
                this.selectedSearchCategoryId = ""; // Aucune catégorie spécifique
                this.searchTriggered = true;
                this.dropdownSelection = false;
                this.searchStarted = true;

                // Appeler fetchMagasins() avec le mot-clé libre
                this.fetchMagasins();
            }
        },

        async fetchMagasins() {
            this.isLoading = true; // Activer l'indicateur de chargement
            this.searchCompleted = false; // Réinitialiser à false avant la recherche

            try {
                const response = await axios.post("/api/magasins", {
                    query: this.searchQuery,
                    category_id: this.selectedSearchCategoryId,
                    location: this.selectedLocation,
                });

                await this.delay(500); // Attendre 1 seconde si nécessaire

                this.allMagasins = response.data.map((magasin) => {
                    return {
                        ...magasin,
                        showPhone: false, // Initialement, tous les magasins cachent le numéro
                    };
                });

                await this.geocodeMagasins();

                this.magasins = this.allMagasins.filter(
                    (magasin) => magasin.distance <= this.selectedRadius
                );

                this.initMap();
                this.updateMapMarkers();
                this.searchStarted = true;

                this.searchCompleted = true; // La recherche est terminée, on peut afficher le conteneur
            } catch (error) {
                console.error(
                    "Erreur lors de la récupération des magasins:",
                    error
                );
                this.magasins = [];
            } finally {
                this.isLoading = false;
                this.searchAttempted = true;
            }
        },
        fetchMagasinsWithRadius() {
            if (this.selectedRadius === 0) {
                // Si le rayon est 0, afficher tous les magasins
                this.magasins = this.allMagasins;
            } else {
                // Vérifier si une catégorie est sélectionnée
                if (this.selectedDropdownCategoryId) {
                    // Appeler fetchMagasinsByCategory si une catégorie est sélectionnée
                    this.fetchMagasinsByCategory(
                        this.selectedDropdownCategoryId
                    );
                } else {
                    // Si aucune catégorie n'est sélectionnée, filtrer uniquement par rayon
                    this.magasins = this.allMagasins.filter((magasin) => {
                        return magasin.distance <= this.selectedRadius;
                    });
                }
            }

            // Mettre à jour la carte avec les magasins filtrés
            this.initMap();
            this.updateMapMarkers(); // Mettre à jour les marqueurs sur la carte avec les magasins filtrés
        },
        async geocodeMagasins() {
            const geocoder = new google.maps.Geocoder();
            const magasinsProches = [];

            const geocodingPromises = this.allMagasins.map((magasin) => {
                return new Promise((resolve, reject) => {
                    geocoder.geocode(
                        { address: magasin.adresse },
                        (results, status) => {
                            if (status === "OK" && results[0]) {
                                const magasinLat =
                                    results[0].geometry.location.lat();
                                const magasinLng =
                                    results[0].geometry.location.lng();

                                // Calculer la distance par rapport à la localisation sélectionnée (Paris)
                                const distance = this.calculateDistance(
                                    this.userLat, // Latitude de l'utilisateur ou de Paris
                                    this.userLng, // Longitude de l'utilisateur ou de Paris
                                    magasinLat,
                                    magasinLng
                                );

                                magasin.distance = distance;

                                // Ajouter les magasins seulement s'ils sont dans le rayon sélectionné
                                if (distance <= this.selectedRadius) {
                                    magasinsProches.push(magasin);
                                }
                                resolve();
                            } else {
                                console.error(
                                    `Erreur de géocodage pour ${magasin.nom}: ${status}`
                                );
                                resolve();
                            }
                        }
                    );
                });
            });

            await Promise.all(geocodingPromises);

            // Trier les magasins proches par distance
            this.magasins = magasinsProches.sort(
                (a, b) => a.distance - b.distance
            );

            this.updateMapMarkers();
        },

        calculateDistance(lat1, lon1, lat2, lon2) {
            const R = 6371; // Rayon de la Terre en km
            const dLat = (lat2 - lat1) * (Math.PI / 180);
            const dLon = (lon2 - lon1) * (Math.PI / 180);
            const a =
                Math.sin(dLat / 2) * Math.sin(dLat / 2) +
                Math.cos(lat1 * (Math.PI / 180)) *
                    Math.cos(lat2 * (Math.PI / 180)) *
                    Math.sin(dLon / 2) *
                    Math.sin(dLon / 2);
            const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
            return R * c; // Distance en km
        },
        async fetchMagasinsByCategory(categoryId) {
            if (!categoryId) return;

            this.isLoading = true; // Activer l'indicateur de chargement
            this.searchStarted = true;

            this.searchCompleted = false;

            try {
                const response = await axios.get(
                    `/api/categories/${categoryId}/magasins`
                );

                await this.delay(500); // Attendre 1 seconde si nécessaire

                // Stocker les données récupérées sans les afficher directement
                this.allMagasins = response.data;

                // Geocoder les magasins pour calculer la distance avant le filtrage par rayon
                await this.geocodeMagasins();

                // Appliquer le filtrage par rayon avant de les afficher
                this.magasins = this.allMagasins.filter(
                    (magasin) => magasin.distance <= this.selectedRadius
                );

                // Mettre à jour la carte et les marqueurs
                this.initMap();
                this.updateMapMarkers(); // Mettre à jour les marqueurs après le filtrage
                this.searchStarted = true;

                this.searchCompleted = true;
            } catch (error) {
                console.error(
                    "Erreur lors de la récupération des magasins:",
                    error
                );
                this.magasins = [];
            } finally {
                this.isLoading = false; // Désactiver l'indicateur de chargement
                this.searchAttempted = true;
            }
        },
        toggleDropdown() {
            this.dropdownOpen = !this.dropdownOpen;
        },
        hideDropdown() {
            setTimeout(() => {
                this.showDropdown = false;
            }, 200);
        },
        toggleRadiusDropdown() {
            this.radiusDropdownOpen = !this.radiusDropdownOpen;
        },
        selectRadius(radius) {
            this.selectedRadius = radius.value;
            this.selectedRadiusName = radius.name;
            this.radiusDropdownOpen = false;
            this.fetchMagasinsWithRadius(); // Appeler la méthode de filtrage des magasins par rayon
        },
    },
};
</script>

<style scoped>
.container {
    display: flex;
    flex-direction: column;
}

/* Store cards container (scrollable) */
.store-cards-container {
    height: 500px; /* Ajustez la hauteur selon vos besoins */
    overflow-y: scroll; /* Permettre le défilement vertical */
    box-sizing: border-box;

    /* Masquer la barre de défilement dans les navigateurs modernes */
    scrollbar-width: none; /* Firefox */
    -ms-overflow-style: none; /* Internet Explorer 10+ */
}

.store-cards-container.w-full {
    width: 100%; /* Occupe toute la largeur pour la vue "Liste" */
}

.store-cards-container.w-demi {
    width: 50% !important; /* Occupe 50% pour la vue "Graph" */
}

.store-cards-container::-webkit-scrollbar {
    display: none; /* Masquer la barre de défilement pour Chrome, Safari et Opera */
}

.map-container {
    width: 100%;
    height: 100%;
}

/* Basic styling for the store cards */
.grid {
    display: grid;
    gap: 20px;
}

.grid-cols-1 {
    grid-template-columns: 1fr;
}

.border {
    border: 1px solid #ccc;
}

.bg-white {
    background-color: #fff;
}

.flex {
    display: flex;
}

.items-start {
    align-items: flex-start;
}
.custom-input:focus {
    outline: none;
    box-shadow: none;
    border-color: #ccc;
}

.relative .fas.fa-search {
    color: #6b7280;
    font-size: 1rem;
    margin-top: 12px;
    margin-left: 10px;
}

.relative .fas.fa-map-marker-alt {
    margin-top: 12px;
    margin-left: 10px;
}

#map {
    height: 500px; /* Ajustez la hauteur selon vos besoins */
    width: 600px;
}
.icon-small {
    font-size: 12px; /* Ajustez la taille selon vos besoins */
}
.rotate-0 {
    transform: rotate(0deg);
}

.rotate-180 {
    transform: rotate(180deg);
}

.transition-transform {
    transition: transform 0.3s ease;
}

.relative .fas.fa-times {
    position: absolute;
    margin-left: 20px;

    transform: translateY(-50%);
}

.relative .fas.fa-times:hover {
    color: #ff0000; /* Change la couleur de l'icône "X" au survol */
}

.relative .fas.fa-times {
    position: absolute;
    left: 385px; /* Ajuste la position de l'icône "X" à droite */
    bottom: 4px;
    transform: translateY(-50%);
}
.loader-container {
    position: fixed;
    top: 0;
    left: 0;
    width: 100vw;
    height: 100vh;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    background-color: rgba(255, 255, 255, 0.8);
    z-index: 9999;
}

.radar-gif {
    width: 150px;
    height: 150px;
    border-radius: 50%; /* Forme circulaire */
    object-fit: contain; /* S'assure que l'image n'est pas déformée */
    background-color: transparent; /* S'assure que le fond est transparent */
}


.store-cards-container {
    margin-bottom: 0 !important; /* Supprime toute marge sur les cartes */
}
.image-container .magasin-image {
    display: block;
}
.rayon{
    display: block;
}
.header{
    display: block;
}

@media (min-width: 320px) and (max-width: 600px) {
    #map {
        width: 210px; 
    }
    .image-container .magasin-image {
        display: none;
    }
    .rayon{
    display: none;
}
    .button-container {
        display: block; /* Ou inline-block selon vos besoins */
    }
    .NavBar{
        display: block;
}


    /* Ajouter éventuellement des marges ou des espacements */
    .button-container button, 
    .button-container a {
        margin-bottom: 10px; /* Espacement entre les éléments */
    }
    .categorie{
    width: 300%;
    margin-bottom: 5px;
    margin-left: 10px;
}
.recherche-bar{
    width: 274%;
}
.header{
    display: none;
}

}
@media (min-width: 600px) and (max-width: 722px) {
    #map {
        width: 330px; 
    }

} 
@media (min-width: 722px) and (max-width: 844px) {
    #map {
        width: 410px; 
    }
} 
@media (min-width: 844px) and (max-width: 968px) {
    #map {
        width: 480px; 
    }
} 
 @media (min-width: 968px) and (max-width: 1110px) {
    #map {
        width: 490px;
    }
}  
</style>
