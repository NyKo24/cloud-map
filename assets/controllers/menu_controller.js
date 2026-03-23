import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
  static targets = ["mobileNav", "desktopNav"];

  connect() {
    this.mobileNav = document.getElementById("mobileNav");
    this.isOpen = false; // Suivi de l'état du menu
  }

  toggle() {
    this.isOpen = !this.isOpen;
    if (this.isOpen) {
      this.mobileNav.classList.remove("hidden");
    } else {
      this.mobileNav.classList.add("hidden");
    }
  }
}
