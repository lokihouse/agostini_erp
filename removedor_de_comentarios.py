import os

def processar_arquivo(caminho_arquivo):
    """
    Lê um arquivo linha por linha e remove comentários de estilo C/C++.

    Args:
        caminho_arquivo (str): O caminho completo para o arquivo.
    """
    try:
        with open(caminho_arquivo, 'r+') as arquivo:
            linhas = arquivo.readlines()
            arquivo.seek(0)
            arquivo.truncate()
            modo_comentario_multilinha = False
            for linha in linhas:
                nova_linha = ''
                i = 0
                while i < len(linha):
                    if modo_comentario_multilinha:
                        if linha[i:i+2] == '*/':
                            modo_comentario_multilinha = False
                            i += 2
                        else:
                            i += 1
                    elif linha[i:i+2] == '//':
                        break  # Ignora o resto da linha
                    elif linha[i:i+2] == '/*':
                        modo_comentario_multilinha = True
                        i += 2
                    else:
                        nova_linha += linha[i]
                        i += 1
                if nova_linha:
                    arquivo.write(nova_linha)
    except Exception as e:
        print(f"Erro ao processar o arquivo {caminho_arquivo}: {e}")

def processar_pasta(caminho_pasta):
    """
    Itera sobre todos os arquivos em uma pasta e os processa.

    Args:
        caminho_pasta (str): O caminho para a pasta contendo os arquivos.
    """
    try:
        for nome_arquivo in os.listdir(caminho_pasta):
            caminho_completo = os.path.join(caminho_pasta, nome_arquivo)
            if os.path.isfile(caminho_completo):
                print(f"Processando o arquivo: {caminho_completo}")
                processar_arquivo(caminho_completo)
    except FileNotFoundError:
        print(f"Erro: A pasta '{caminho_pasta}' não foi encontrada.")
    except Exception as e:
        print(f"Erro ao processar a pasta '{caminho_pasta}': {e}")

if __name__ == "__main__":
    pasta_alvo = input("Digite o caminho da pasta que você deseja processar: ")
    processar_pasta(pasta_alvo)
    print("Processamento concluído!")
