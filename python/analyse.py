#!/usr/bin/env python3
# -*- coding: utf-8 -*-

import mysql.connector
import pandas as pd
import matplotlib.pyplot as plt
import seaborn as sns
from sklearn.linear_model import LinearRegression
import numpy as np
import os
import json
from datetime import datetime

# Configuration base de données
DB_CONFIG = {
    'host': '127.0.0.1',
    'user': 'dev_user',
    'password': 'jinq123123@',
    'database': 'immobilier_db'
}

# Dossier de sortie (accessible depuis le web)
OUTPUT_DIR = '/var/www/html/assets/stats/'
os.makedirs(OUTPUT_DIR, exist_ok=True)

def get_connection():
    return mysql.connector.connect(**DB_CONFIG)

def load_data():
    conn = get_connection()
    # Charger les biens
    biens = pd.read_sql("SELECT * FROM biens", conn)
    # Charger les achats (ventes simulées)
    ventes = pd.read_sql("""
        SELECT a.*, b.prix, b.ville, b.surface, b.type, b.date_creation
        FROM achats a
        JOIN biens b ON a.bien_id = b.id
    """, conn)
    conn.close()
    return biens, ventes

def clean_data(biens, ventes):
    # Supprimer les lignes avec valeurs manquantes critiques
    biens = biens.dropna(subset=['prix', 'surface', 'ville', 'type'])
    biens['prix_m2'] = biens['prix'] / biens['surface']
    # Nettoyer les dates
    biens['date_creation'] = pd.to_datetime(biens['date_creation'])
    if not ventes.empty and 'date_achat' in ventes.columns:
        ventes['date_achat'] = pd.to_datetime(ventes['date_achat'])
    return biens, ventes

def generate_statistics(biens, ventes):
    stats = {}
    # Prix moyen par ville
    prix_moyen_ville = biens.groupby('ville')['prix'].mean().sort_values(ascending=False).head(10)
    stats['prix_moyen_ville'] = prix_moyen_ville.to_dict()
    # Prix au m² moyen par ville
    prix_m2_ville = biens.groupby('ville')['prix_m2'].mean().sort_values(ascending=False).head(10)
    stats['prix_m2_ville'] = prix_m2_ville.to_dict()
    # Répartition par type
    stats['type_count'] = biens['type'].value_counts().to_dict()
    # Nombre de ventes par mois
    if not ventes.empty:
        ventes['mois'] = ventes['date_achat'].dt.to_period('M')
        ventes_par_mois = ventes.groupby('mois').size()
        stats['ventes_par_mois'] = {str(k): v for k, v in ventes_par_mois.to_dict().items()}
    else:
        stats['ventes_par_mois'] = {}
    # Biens les plus consultés (top 5)
    top_vues = biens.nlargest(5, 'vue_count')[['titre', 'vue_count']].to_dict(orient='records')
    stats['top_vues'] = top_vues
    return stats

def generate_charts(biens, ventes):
    # 1. Prix moyen par ville (barres horizontales)
    plt.figure(figsize=(10,6))
    prix_ville = biens.groupby('ville')['prix'].mean().sort_values(ascending=True).tail(10)
    prix_ville.plot(kind='barh', color='skyblue')
    plt.title('Prix moyen par ville (top 10)')
    plt.xlabel('Prix (€)')
    plt.tight_layout()
    plt.savefig(OUTPUT_DIR + 'prix_moyen_ville.png')
    plt.close()

    # 2. Prix au m² par ville
    plt.figure(figsize=(10,6))
    prix_m2_ville = biens.groupby('ville')['prix_m2'].mean().sort_values(ascending=True).tail(10)
    prix_m2_ville.plot(kind='barh', color='lightgreen')
    plt.title('Prix moyen au m² par ville (top 10)')
    plt.xlabel('Prix au m² (€)')
    plt.tight_layout()
    plt.savefig(OUTPUT_DIR + 'prix_m2_ville.png')
    plt.close()

    # 3. Répartition des types de biens (camembert)
    type_counts = biens['type'].value_counts()
    plt.figure(figsize=(8,8))
    type_counts.plot(kind='pie', autopct='%1.1f%%', startangle=90)
    plt.title('Répartition des types de biens')
    plt.ylabel('')
    plt.tight_layout()
    plt.savefig(OUTPUT_DIR + 'type_repartition.png')
    plt.close()

    # 4. Évolution des ventes par mois (si données disponibles)
    if not ventes.empty and len(ventes) > 1:
        ventes['mois'] = ventes['date_achat'].dt.to_period('M').astype(str)
        ventes_mois = ventes.groupby('mois').size()
        plt.figure(figsize=(12,5))
        ventes_mois.plot(kind='line', marker='o')
        plt.title('Nombre de ventes par mois')
        plt.xlabel('Mois')
        plt.ylabel('Ventes')
        plt.xticks(rotation=45)
        plt.tight_layout()
        plt.savefig(OUTPUT_DIR + 'ventes_mois.png')
        plt.close()
    else:
        # Générer un graphique vide ou message
        pass

    # 5. Top 5 des biens les plus consultés
    top5 = biens.nlargest(5, 'vue_count')[['titre', 'vue_count']]
    plt.figure(figsize=(10,6))
    plt.barh(top5['titre'], top5['vue_count'], color='orange')
    plt.title('Top 5 des biens les plus consultés')
    plt.xlabel('Nombre de vues')
    plt.tight_layout()
    plt.savefig(OUTPUT_DIR + 'top_vues.png')
    plt.close()

def simple_prediction(biens):
    # Prédiction simple : prix en fonction de la surface (régression linéaire)
    df = biens.dropna(subset=['surface', 'prix'])
    if len(df) < 2:
        return {}
    X = df[['surface']].values
    y = df['prix'].values
    model = LinearRegression()
    model.fit(X, y)
    # Prédire pour une surface de 50, 80, 100 m²
    surfaces_test = [[50], [80], [100]]
    predictions = model.predict(surfaces_test)
    result = {int(s[0]): round(pred, 2) for s, pred in zip(surfaces_test, predictions)}
    # Sauvegarder le modèle (facultatif)
    import joblib
    joblib.dump(model, OUTPUT_DIR + 'prix_model.pkl')
    return result

def generate_report():
    biens, ventes = load_data()
    biens, ventes = clean_data(biens, ventes)
    stats = generate_statistics(biens, ventes)
    generate_charts(biens, ventes)
    predictions = simple_prediction(biens)
    
    # Sauvegarder les statistiques en JSON
    with open(OUTPUT_DIR + 'stats.json', 'w', encoding='utf-8') as f:
        json.dump(stats, f, indent=2, ensure_ascii=False)
    
    # Sauvegarder les prédictions
    with open(OUTPUT_DIR + 'predictions.json', 'w', encoding='utf-8') as f:
        json.dump(predictions, f, indent=2)
    
    print("Analyse terminée. Fichiers générés dans", OUTPUT_DIR)

if __name__ == "__main__":
    generate_report()