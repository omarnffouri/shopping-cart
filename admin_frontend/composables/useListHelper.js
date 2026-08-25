export function useListHelper() {

    const cbList = ref([]);
    const listPageRef = ref(null);
    const itemList = ref([]);

    const setItemList = (event) => {
        itemList.value = event;
    };

    const deleteBulk = () => {
        if (cbList.value?.length) {
            listPageRef.value.deleteItem(cbList.value.join(','));
        }
    };

    const checkAll = (evt) => {
        if (evt.target.checked) {
            cbList.value = itemList.value.map(i => {
                return i.id
            })
        } else {
            cbList.value = []
        }
    };

    const editNode = (node) => {
        listPageRef.value.editItem(node.id);
    };

    const deleteNode = (node) => {
        listPageRef.value.deleteItem(node.id);
    };


    return {
        cbList, listPageRef, deleteBulk, setItemList, checkAll, editNode, deleteNode, itemList
    }
}


