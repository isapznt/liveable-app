import { ref } from "vue";

export const exibir = ref<boolean>(false);

export function exibirConfirm() {
  exibir.value = !exibir.value;
}
