import React, { useState } from "react";
const BillingUI = () => {
  const [barcode, setBarcode] = useState("");
  const [products, setProducts] = useState([]);
  const [count, setCount] = useState(1); // count input control
  // Fetch product from API
  const handleFetch = async () => {
    try {
      const response = await fetch("http://localhost/barcode_api/getproduct.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ barcode }),
      });
      const data = await response.json();
      if (data.success) {
        setProducts([
          ...products,
          { ...data.product, id: products.length + 1, barcode },
        ]);
        setBarcode("");
      } else {
        alert("Product not found");
      }
    } catch (err) {
      console.error("Error fetching product:", err);
      alert("Error fetching product");
    }
  };
  // Reset everything
  const handleReset = () => {
    setBarcode("");
    setProducts([]);
    setCount(1);
  };
  const handleChange = (index, field, value) => {
    const updatedProducts = [...products];
    updatedProducts[index][field] = value;
    setProducts(updatedProducts);
  };
  // Styles
  const pageStyle = {
    fontFamily: "Arial, sans-serif",
    padding: "20px",
    background: "linear-gradient(135deg, #F9F9F9, #E6F7FF)",
    minHeight: "100vh",
  };
  const topStyle = {
    display: "flex",
    flexWrap: "wrap",
    gap: "10px",
    marginBottom: "20px",
    alignItems: "center",
  };
  const inputStyle = {
    padding: "10px",
    fontSize: "16px",
    borderRadius: "8px",
    border: "1px solid #ccc",
    flex: "1 1 200px",
    minWidth: "150px",
  };
  const fetchBtnStyle = {
    padding: "8px 12px",
    background: "#007BFF",
    color: "white",
    border: "none",
    borderRadius: "6px",
    cursor: "pointer",
    flex: "0 0 80px",
  };
  const resetBtnStyle = {
    padding: "8px 12px",
    background: "#DC3545",
    color: "white",
    border: "none",
    borderRadius: "6px",
    cursor: "pointer",
    flex: "0 0 80px",
  };
  const productRowStyle = {
    display: "flex",
    flexWrap: "wrap",
    gap: "10px",
    marginBottom: "10px",
    alignItems: "center",
  };
  const fieldStyle = {
    padding: "10px",
    fontSize: "16px",
    borderRadius: "8px",
    border: "1px solid #ccc",
    textAlign: "center",
  };
  const sNoStyle = { ...fieldStyle, flex: "0 0 40px", minWidth: "40px" };
  const barcodeStyle = { ...fieldStyle, flex: "1 1 120px", minWidth: "120px" };
  const nameStyle = { ...fieldStyle, flex: "1 1 120px", minWidth: "120px" };
  const categoryStyle = { ...fieldStyle, flex: "1 1 120px", minWidth: "120px" };
  const priceStyle = { ...fieldStyle, flex: "1 1 80px", minWidth: "80px" };
  const countPrintStyle = {
    display: "flex",
    flexWrap: "wrap",
    gap: "10px",
    marginTop: "20px",
    alignItems: "center",
  };
  const countInputStyle = {
    padding: "10px",
    fontSize: "16px",
    borderRadius: "8px",
    border: "1px solid #ccc",
    width: "80px",
  };
  const printBtnStyle = {
    padding: "10px 20px",
    background: "#28A745",
    color: "white",
    border: "none",
    borderRadius: "8px",
    cursor: "pointer",
  };
  const barcodePreviewStyle = {
    marginTop: "30px",
    padding: "20px",
    border: "2px dashed #ccc",
    borderRadius: "8px",
    textAlign: "center",
    color: "#888",
  };
  return (
    <div style={pageStyle}>
      {/* Top Barcode Input + Fetch + Reset */}
      <div style={topStyle}>
        <input
          type="text"
          placeholder="Enter Barcode"
          value={barcode}
          onChange={(e) => setBarcode(e.target.value)}
          style={inputStyle}
        />
        <button style={fetchBtnStyle} onClick={handleFetch}>
          Fetch
        </button>
        <button style={resetBtnStyle} onClick={handleReset}>
          Reset
        </button>
      </div>
      {/* Product List */}
      {products.length === 0 ? (
        <p>No products yet</p>
      ) : (
        products.map((p, index) => (
          <div key={index} style={productRowStyle}>
            <input type="text" value={index + 1} readOnly style={sNoStyle} />
            <input type="text" value={p.barcode} readOnly style={barcodeStyle} />
            <input
              type="text"
              value={p.name}
              onChange={(e) => handleChange(index, "name", e.target.value)}
              style={nameStyle}
            />
            <input
              type="text"
              value={p.category}
              onChange={(e) => handleChange(index, "category", e.target.value)}
              style={categoryStyle}
            />
            <input
              type="number"
              value={p.price}
              onChange={(e) => handleChange(index, "price", e.target.value)}
              style={priceStyle}
            />
          </div>
        ))
      )}
      {/* Count + Print */}
      <div style={countPrintStyle}>
        <input
          type="number"
          value={count}
          onChange={(e) => setCount(e.target.value)}
          style={countInputStyle}
        />
        <button style={printBtnStyle}>Print</button>
      </div>
      {/* Barcode Preview */}
      <div style={barcodePreviewStyle}>
        <p>[ Barcode Preview ]</p>
      </div>
    </div>
  );
};
export default BillingUI;